<script setup>
// History page for browsing previous prompt generations.
import { computed, onMounted, ref } from "vue";
import HistoryFilters from "../components/HistoryFilters.vue";
import HistoryTable from "../components/HistoryTable.vue";
import PaginationControls from "../components/PaginationControls.vue";
import InlineAlert from "../components/InlineAlert.vue";
import {
    deletePromptHistoryItem,
    fetchPromptHistory,
} from "../services/promptGenerationService";

const loading = ref(false);
const deletingId = ref(null);
const errorMessage = ref("");
const infoMessage = ref("");

const filterModel = ref({
    search: "",
    mimeType: "",
    perPage: 6,
    sortValue: "created_at:desc",
});

const historyState = ref({
    items: [],
    currentPage: 1,
    perPage: 6,
    total: 0,
    lastPage: 1,
});

// Splits combined select value into sort field and direction values.
const sortParams = computed(() => {
    const [sortBy, sortDirection] = filterModel.value.sortValue.split(":");
    return {
        sortBy,
        sortDirection,
    };
});

// Loads history from backend and updates local state.
async function loadHistory(page = 1) {
    loading.value = true;
    errorMessage.value = "";
    infoMessage.value = "";

    try {
        const result = await fetchPromptHistory({
            page,
            perPage: filterModel.value.perPage,
            search: filterModel.value.search,
            mimeType: filterModel.value.mimeType,
            sortBy: sortParams.value.sortBy,
            sortDirection: sortParams.value.sortDirection,
        });

        historyState.value = result;
        if (!result.items.length) {
            infoMessage.value =
                "No history records found with current filters.";
        }
    } catch (error) {
        errorMessage.value = error?.message || "Failed to load history.";
    } finally {
        loading.value = false;
    }
}

// Applies filters by reloading from the first page.
function applyFilters() {
    loadHistory(1);
}

// Copies prompt text from history item.
async function copyFromHistory(promptText) {
    if (!promptText) return;

    await navigator.clipboard.writeText(promptText);
    infoMessage.value = "Prompt copied to clipboard.";
    window.setTimeout(() => {
        infoMessage.value = "";
    }, 1800);
}

async function deleteFromHistory(id) {
    if (!id || deletingId.value) return;

    const confirmed = window.confirm(
        "Delete this prompt from your history? This action cannot be undone.",
    );

    if (!confirmed) return;

    deletingId.value = id;
    errorMessage.value = "";
    infoMessage.value = "";

    try {
        await deletePromptHistoryItem(id);

        const currentPage = historyState.value.currentPage || 1;
        const hasSingleItemOnPage = historyState.value.items.length === 1;
        const targetPage =
            hasSingleItemOnPage && currentPage > 1 ? currentPage - 1 : currentPage;

        await loadHistory(targetPage);
        infoMessage.value = "Prompt deleted successfully.";
    } catch (error) {
        errorMessage.value = error?.message || "Failed to delete prompt.";
    } finally {
        deletingId.value = null;
    }
}

onMounted(() => {
    loadHistory(1);
});
</script>

<template>
    <section class="history-page">
        <div class="page-head">
            <h2 class="section-title">Generation History</h2>
            <p class="muted">
                Search, filter, sort, and paginate previous image prompt
                generations.
            </p>
        </div>

        <HistoryFilters v-model="filterModel" @search="applyFilters" />

        <InlineAlert v-if="errorMessage" type="error" :message="errorMessage" />
        <InlineAlert v-if="infoMessage" type="info" :message="infoMessage" />

        <p v-if="loading" class="loading-text">Loading history...</p>

        <template v-else>
            <HistoryTable
                :items="historyState.items"
                :deleting-id="deletingId"
                @copy="copyFromHistory"
                @delete="deleteFromHistory"
            />
            <PaginationControls
                :current-page="historyState.currentPage"
                :last-page="historyState.lastPage"
                :total="historyState.total"
                @page-change="loadHistory"
            />
        </template>
    </section>
</template>

<style scoped>
.history-page {
    animation: reveal 380ms ease;
}

.page-head {
    margin-bottom: 0.75rem;
}

.loading-text {
    margin: 1rem 0;
    font-weight: 600;
    color: var(--text-secondary);
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
