<script setup>
// Result card that shows generated prompt text and copy action.
import { computed, ref } from "vue";
import { formatBytes, formatDateTime } from "../utils/formatters";

const props = defineProps({
    item: {
        type: Object,
        default: null,
    },
});

const copied = ref(false);

// Returns true when the API has a prompt string to show.
const hasPrompt = computed(() => Boolean(props.item?.generated_prompt));

// Copies prompt to clipboard so user can reuse it quickly.
async function copyPrompt() {
    if (!props.item?.generated_prompt) return;

    await navigator.clipboard.writeText(props.item.generated_prompt);
    copied.value = true;
    window.setTimeout(() => {
        copied.value = false;
    }, 1800);
}
async function deletePrompt() {
     if (!props.item?.generated_prompt) return;
     await props.$emit('delete', props.item.id);
}
</script>

<template>
    <article class="result-card glass-card">
        <div class="result-header">
            <h2 class="section-title">Generated Prompt</h2>
            <button
                class="btn btn-secondary"
                type="button"
                :disabled="!hasPrompt"
                @click="copyPrompt"
            >
                {{ copied ? "Copied" : "Copy Prompt" }}
            </button>
            
        </div>

        <p v-if="hasPrompt" class="prompt-body">{{ item.generated_prompt }}</p>
        <p v-else class="muted">
            No generated prompt yet. Upload an image to begin.
        </p>

        <div v-if="item" class="meta-row">
            <span class="chip">{{ item.mime_type || "N/A" }}</span>
            <span class="chip">{{ formatBytes(item.file_size) }}</span>
            <span class="chip">{{ formatDateTime(item.created_at) }}</span>
        </div>
    </article>
</template>

<style scoped>
.result-card {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.result-header {
    display: flex;
    justify-content: space-between;
    gap: 0.8rem;
    align-items: center;
}

.prompt-body {
    margin: 0;
    white-space: pre-wrap;
    background: var(--surface-muted);
    border: 1px solid var(--stroke-soft);
    border-radius: 12px;
    padding: 0.8rem;
    line-height: 1.5;
    max-height: 320px;
    overflow: auto;
}

.meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}
</style>
