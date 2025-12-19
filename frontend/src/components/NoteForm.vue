<!-- src/components/NoteForm.vue -->
<template>
  <div class="note-form">
    <div class="form-header">
      <h2>{{ isEditing ? 'Редактировать заметку' : 'Новая заметка' }}</h2>
    </div>

    <form @submit.prevent="handleSubmit" class="form">
      <div class="form-group">
        <label for="title">Заголовок</label>
        <input
            type="text"
            id="title"
            v-model="form.title"
            placeholder="Введите заголовок"
            :disabled="store.isLoading"
            class="form-input"
        />
        <div v-if="errors.title" class="error-message">
          {{ errors.title }}
        </div>
      </div>

      <div class="form-group">
        <label for="content">Содержимое</label>
        <textarea
            id="content"
            v-model="form.content"
            placeholder="Введите содержимое заметки"
            rows="10"
            :disabled="store.isLoading"
            class="form-textarea"
        ></textarea>
        <div v-if="errors.content" class="error-message">
          {{ errors.content }}
        </div>
      </div>

      <div class="form-actions">
        <button
            type="submit"
            :disabled="store.isLoading || !isFormValid"
            class="btn-submit"
        >
          {{ store.isLoading ? 'Сохранение...' : (isEditing ? 'Сохранить' : 'Создать') }}
        </button>

        <button
            v-if="isEditing"
            type="button"
            @click="handleCancel"
            :disabled="store.isLoading"
            class="btn-cancel"
        >
          Отмена
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useNoteStore } from '../stores/noteStore';
import type { Note } from '../types/note';

const props = defineProps<{
  note?: Note | null;
}>();

const emit = defineEmits<{
  saved: []
  cancelled: []
}>();

const store = useNoteStore();

const form = ref({
  title: '',
  content: ''
});

const errors = ref({
  title: '',
  content: ''
});

const isEditing = computed(() => !!props.note?.id);

// Валидация формы
const isFormValid = computed(() => {
  return form.value.title.trim().length > 0;
});

// Сброс формы
const resetForm = () => {
  form.value = {
    title: '',
    content: ''
  };
  errors.value = {
    title: '',
    content: ''
  };
};

// Заполнение формы при редактировании
watch(() => props.note, (newNote) => {
  if (newNote) {
    form.value = {
      title: newNote.title,
      content: newNote.content || ''
    };
  } else {
    resetForm();
  }
}, { immediate: true });

// Валидация перед отправкой
const validateForm = (): boolean => {
  let isValid = true;

  if (!form.value.title.trim()) {
    errors.value.title = 'Заголовок обязателен';
    isValid = false;
  } else if (form.value.title.length > 255) {
    errors.value.title = 'Заголовок слишком длинный';
    isValid = false;
  } else {
    errors.value.title = '';
  }

  return isValid;
};

// Обработка отправки формы
const handleSubmit = async () => {
  if (!validateForm()) {
    return;
  }

  try {
    if (isEditing.value && props.note?.id) {
      await store.updateNote(props.note.id, {
        title: form.value.title,
        content: form.value.content
      });
    } else {
      await store.createNote({
        title: form.value.title,
        content: form.value.content
      });
    }

    resetForm();
    emit('saved');
  } catch (error) {
    // Ошибки уже обрабатываются в store
  }
};

const handleCancel = () => {
  resetForm();
  emit('cancelled');
};

onMounted(() => {
  resetForm();
});
</script>

<style scoped>
.note-form {
  padding: 1.5rem;
  height: 100vh;
  overflow-y: auto;
  background: white;
}

.form-header {
  margin-bottom: 1.5rem;
}

.form-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #333;
}

.form {
  max-width: 600px;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #495057;
}

.form-input,
.form-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 1rem;
  font-family: inherit;
  transition: border-color 0.2s;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-input:disabled,
.form-textarea:disabled {
  background-color: #e9ecef;
  cursor: not-allowed;
}

.form-textarea {
  resize: vertical;
  min-height: 150px;
}

.error-message {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-submit {
  background: #007bff;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  flex: 1;
}

.btn-submit:hover:not(:disabled) {
  background: #0056b3;
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-cancel {
  background: #6c757d;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}

.btn-cancel:hover:not(:disabled) {
  background: #545b62;
}
</style>