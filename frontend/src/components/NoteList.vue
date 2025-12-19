<!-- src/components/NoteList.vue -->
<template>
  <div class="note-list">
    <div class="list-header">
      <h2>Мои заметки</h2>
      <button
          @click="handleCreateNew"
          class="btn-new"
          :disabled="store.isLoading"
      >
        + Новая заметка
      </button>
    </div>

    <div v-if="store.isLoading && store.notes.length === 0" class="loading">
      Загрузка заметок...
    </div>

    <div v-else-if="store.error" class="error">
      {{ store.error }}
    </div>

    <div v-else-if="store.notes.length === 0" class="empty">
      Заметок пока нет. Создайте первую!
    </div>

    <ul v-else class="notes">
      <li
          v-for="note in store.sortedNotes"
          :key="note.id"
          :class="{ active: note.id === store.currentNote?.id }"
          @click="handleSelectNote(note)"
          class="note-item"
      >
        <div class="note-header">
          <h3>{{ note.title || 'Без названия' }}</h3>
          <button
              @click.stop="handleDeleteNote(note.id!)"
              class="btn-delete"
              :disabled="store.isLoading"
          >
            ✕
          </button>
        </div>
        <p class="note-preview">{{ truncateContent(note.content) }}</p>
        <div class="note-date">
          {{ formatDate(note.updatedAt) }}
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { useNoteStore } from '../stores/noteStore';
import type { Note } from '../types/note';

const store = useNoteStore();

const emit = defineEmits<{
  selectNote: [note: Note]
  createNew: []
}>();

const truncateContent = (content: string = '', length: number = 100): string => {
  if (content.length <= length) return content;
  return content.substring(0, length) + '...';
};

const formatDate = (dateString?: string): string => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const handleSelectNote = (note: Note) => {
  store.setCurrentNote(note);
  emit('selectNote', note);
};

const handleDeleteNote = async (id: number) => {
  if (confirm('Удалить эту заметку?')) {
    await store.deleteNote(id);
  }
};

const handleCreateNew = () => {
  store.resetCurrentNote();
  emit('createNew');
};
</script>

<style scoped>
.note-list {
  background: #f8f9fa;
  border-right: 1px solid #dee2e6;
  height: 100vh;
  overflow-y: auto;
}

.list-header {
  padding: 1.5rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  position: sticky;
  top: 0;
  z-index: 10;
}

.list-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #333;
}

.btn-new {
  background: #007bff;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-new:hover:not(:disabled) {
  background: #0056b3;
}

.btn-new:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.loading, .error, .empty {
  padding: 2rem;
  text-align: center;
  color: #6c757d;
}

.error {
  color: #dc3545;
}

.notes {
  list-style: none;
  padding: 0;
  margin: 0;
}

.note-item {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e9ecef;
  cursor: pointer;
  transition: background-color 0.2s;
}

.note-item:hover {
  background-color: #e9ecef;
}

.note-item.active {
  background-color: #e3f2fd;
  border-left: 3px solid #007bff;
}

.note-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.note-header h3 {
  margin: 0;
  font-size: 1rem;
  color: #333;
  flex: 1;
}

.btn-delete {
  background: none;
  border: none;
  color: #dc3545;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 0 0.5rem;
  opacity: 0.5;
}

.btn-delete:hover {
  opacity: 1;
}

.note-preview {
  margin: 0 0 0.5rem 0;
  color: #6c757d;
  font-size: 0.9rem;
  line-height: 1.4;
}

.note-date {
  font-size: 0.8rem;
  color: #adb5bd;
}
</style>