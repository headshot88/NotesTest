<!-- src/App.vue -->
<template>
  <div class="app">
    <div class="app-container">
      <NoteList
          @select-note="handleSelectNote"
          @create-new="handleCreateNew"
      />
      <NoteForm
          :note="currentNote"
          @saved="handleSaved"
          @cancelled="handleCancelled"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import NoteList from './components/NoteList.vue';
import NoteForm from './components/NoteForm.vue';
import { useNoteStore } from './stores/noteStore';
import type { Note } from './types/note';

const store = useNoteStore();
const currentNote = ref<Note | null>(null);

onMounted(async () => {
  await store.fetchNotes();
});

const handleSelectNote = (note: Note) => {
  currentNote.value = note;
};

const handleCreateNew = () => {
  currentNote.value = null;
};

const handleSaved = async () => {
  currentNote.value = null;
  await store.fetchNotes();
};

const handleCancelled = () => {
  currentNote.value = null;
};
</script>

<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  line-height: 1.6;
  color: #333;
  background-color: #f5f5f5;
}

.app {
  min-height: 100vh;
}

.app-container {
  display: grid;
  grid-template-columns: 350px 1fr;
  height: 100vh;
}

@media (max-width: 768px) {
  .app-container {
    grid-template-columns: 1fr;
  }
}
</style>