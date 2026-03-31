<script setup>
// Presentational list for history records.
import { formatBytes, formatDateTime } from "../utils/formatters";

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    deletingId: {
        type: [Number, String, null],
        default: null,
    },
});

const emit = defineEmits(["copy", "delete"]);

// Emits selected prompt content so parent can execute copy and show feedback.
function copyText(prompt) {
    emit("copy", prompt);
}

function deleteItem(id) {
    emit("delete", id);
}
</script>

<template>
    <section class="history-list glass-card">
        <div v-if="!items.length" class="empty-state">
            <h3>No results found</h3>
            <p class="muted">Try changing your search/filter values.</p>
        </div>

        <article v-for="item in items" :key="item.id" class="history-item">
            <img
                v-if="item.image_url"
                :src="item.image_url"
                alt="Generated input"
                class="history-thumb"
            />
            <div class="history-body">
                <p class="history-text">
                    {{ item.generated_prompt || "No prompt available." }}
                </p>
                <div class="history-meta">
                    <span class="chip">{{ item.mime_type || "N/A" }}</span>
                    <span class="chip">{{ formatBytes(item.file_size) }}</span>
                    <span class="chip">{{
                        formatDateTime(item.created_at)
                    }}</span>
                </div>
            </div>
            <div class="history-actions">
                <button
                    class="btn btn-secondary"
                    type="button"
                    @click="copyText(item.generated_prompt)"
                >
                    Copy
                </button>
                <button
                    class="btn btn-danger"
                    type="button"
                    :disabled="deletingId === item.id"
                    @click="deleteItem(item.id)"
                >
                    {{ deletingId === item.id ? "Deleting..." : "Delete" }}
                </button>
            </div>
        </article>
    </section>
</template>

<style scoped>
.history-list {
    padding: 0.8rem;
}

.history-item {
    display: grid;
    grid-template-columns: 86px 1fr auto;
    gap: 0.8rem;
    align-items: start;
    padding: 0.75rem;
    border-radius: 12px;
    border: 1px solid var(--stroke-soft);
    background: var(--surface);
    transition:
        transform 0.2s ease,
        border-color 0.25s ease,
        background 0.25s ease;
}

.history-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.history-actions .btn {
    min-width: 92px;
}

.history-item:hover {
    transform: scale(1.01);
    border-color: var(--accent);
    background: linear-gradient(
        180deg,
        var(--surface),
        rgba(124, 58, 237, 0.08)
    );
}

.history-item + .history-item {
    margin-top: 0.65rem;
}

.history-thumb {
    width: 86px;
    height: 86px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid var(--stroke-soft);
}

.history-text {
    margin: 0;
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.history-meta {
    margin-top: 0.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.empty-state {
    padding: 1rem;
    text-align: center;
}

.empty-state h3 {
    margin: 0;
    font-family: var(--font-heading);
}

@media (max-width: 940px) {
    .history-item {
        grid-template-columns: 1fr;
    }

    .history-thumb {
        width: 100%;
        height: 170px;
    }
}

@media (max-width: 640px) {
    .history-item {
        grid-template-columns: 74px 1fr;
        gap: 0.55rem;
        align-items: start;
        padding: 0.62rem;
    }

    .history-thumb {
        width: 74px;
        height: 74px;
        object-fit: cover;
    }

    .history-text {
        font-size: 0.92rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .history-meta {
        margin-top: 0.35rem;
    }

    .history-item .btn {
        width: 100%;
    }

    .history-actions {
        grid-column: 1 / -1;
    }
}
</style>
