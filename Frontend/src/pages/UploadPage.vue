<script setup>
// Upload page handles complete image -> prompt generation flow.
import { computed, onBeforeUnmount, ref } from "vue";
import FileDropzone from "../components/FileDropzone.vue";
import GeneratedPromptCard from "../components/GeneratedPromptCard.vue";
import InlineAlert from "../components/InlineAlert.vue";
import { uploadImageAndGeneratePrompt } from "../services/promptGenerationService";
import { getToken } from "../services/apiClient";
import {
    hasAuthToken,
    validateImageDimensions,
    validateImageFile,
} from "../utils/validators";

const selectedFile = ref(null);
const previewUrl = ref("");
const latestResult = ref(null);
const loading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");

// Button is disabled unless valid file exists and request is not in progress.
const canSubmit = computed(() => {
    const validation = validateImageFile(selectedFile.value);
    return validation.valid && !loading.value && hasAuthToken(getToken());
});

// Updates selected image and creates local preview URL.
async function setFile(file) {
    const validation = validateImageFile(file);

    selectedFile.value = file;
    errorMessage.value = validation.message;
    successMessage.value = "";

    if (!validation.valid) {
        clearPreview();
        return;
    }

    const dimensionsValidation = await validateImageDimensions(file);
    if (!dimensionsValidation.valid) {
        errorMessage.value = dimensionsValidation.message;
        clearPreview();
        return;
    }

    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = URL.createObjectURL(file);
}

// Frees object URL memory when file is removed or page is unmounted.
function clearPreview() {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = "";
}

// Sends image to API and stores generated prompt response.
async function submitGeneration() {
    if (!canSubmit.value) return;

    loading.value = true;
    errorMessage.value = "";
    successMessage.value = "";

    try {
        latestResult.value = await uploadImageAndGeneratePrompt(
            selectedFile.value,
        );
        successMessage.value = "Prompt generated successfully.";
    } catch (error) {
        errorMessage.value =
            error?.message || "Could not generate prompt at this time.";
    } finally {
        loading.value = false;
    }
}

// Reuses current selected file to quickly request another generated prompt.
function regenerate() {
    submitGeneration();
}

onBeforeUnmount(() => {
    clearPreview();
});
</script>

<template>
    <section class="upload-page">
        <div class="page-head">
            <h2 class="section-title">Turn Images Into Cinematic Prompts</h2>
            <p class="muted">
                Drop an image, generate prompt text, and copy it instantly.
            </p>
        </div>

        <div class="grid-2">
            <article class="glass-card upload-panel">
                <FileDropzone @file-selected="setFile" />

                <InlineAlert
                    v-if="errorMessage"
                    type="error"
                    :message="errorMessage"
                />
                <InlineAlert
                    v-if="successMessage"
                    type="success"
                    :message="successMessage"
                />

                <figure v-if="previewUrl" class="preview-wrap">
                    <img
                        :src="previewUrl"
                        alt="Selected image preview"
                        class="preview-image"
                    />
                    <figcaption class="muted">
                        Preview of selected image
                    </figcaption>
                </figure>

                <div class="actions">
                    <button
                        class="btn btn-primary"
                        type="button"
                        :disabled="!canSubmit"
                        @click="submitGeneration"
                    >
                        {{ loading ? "Generating..." : "Generate Prompt" }}
                    </button>
                    <button
                        class="btn btn-ghost"
                        type="button"
                        :disabled="!latestResult || loading"
                        @click="regenerate"
                    >
                        Regenerate
                    </button>
                </div>

                <p v-if="!hasAuthToken(getToken())" class="muted helper-note">
                    Please login or register first to continue.
                </p>
            </article>

            <GeneratedPromptCard :item="latestResult" />
        </div>
    </section>
</template>

<style scoped>
.upload-page {
    animation: reveal 380ms ease;
    min-height: calc(100vh - 250px);
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding-top: clamp(1.5rem, 6vh, 4.5rem);
}

.page-head {
    margin-bottom: 0.75rem;
    text-align: center;
}

.page-head p {
    max-width: 640px;
    margin: 0.35rem auto 0;
}

.upload-panel {
    padding: 1rem;
}

.preview-wrap {
    margin: 0.8rem 0 0;
}

.preview-image {
    width: 100%;
    max-height: 340px;
    object-fit: contain;
    border-radius: 12px;
    border: 1px solid var(--stroke-soft);
    background: var(--surface-muted);
}

.actions {
    margin-top: 0.85rem;
    display: flex;
    gap: 0.5rem;
}

.helper-note {
    margin-top: 0.65rem;
}

@media (max-width: 940px) {
    .upload-page {
        min-height: auto;
        padding-top: 1rem;
        justify-content: flex-start;
    }
}

@media (max-width: 640px) {
    .upload-panel {
        padding: 0.85rem;
    }

    .preview-wrap {
        margin-top: 0.65rem;
    }

    .preview-image {
        max-height: 210px;
        width: 88%;
        margin: 0 auto;
        display: block;
    }
}

@keyframes reveal {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
