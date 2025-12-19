// src/stores/noteStore.ts
import { defineStore } from 'pinia';
import { noteService } from '../services/api';
import type { Note } from '../types/note';

interface NoteState {
    notes: Note[];
    currentNote: Note | null;
    isLoading: boolean;
    error: string | null;
}

export const useNoteStore = defineStore('note', {
    state: (): NoteState => ({
        notes: [],
        currentNote: null,
        isLoading: false,
        error: null,
    }),

    actions: {
        // Загрузить все заметки
        async fetchNotes() {
            this.isLoading = true;
            this.error = null;
            try {
                this.notes = await noteService.getAll();
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to fetch notes';
                console.error('Error fetching notes:', error);
            } finally {
                this.isLoading = false;
            }
        },

        // Создать заметку
        async createNote(noteData: Omit<Note, 'id'>) {
            this.isLoading = true;
            this.error = null;
            try {
                const newNote = await noteService.create(noteData);
                this.notes.unshift(newNote); // Добавляем в начало списка
                return newNote;
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to create note';
                console.error('Error creating note:', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Обновить заметку
        async updateNote(id: number, noteData: Partial<Note>) {
            this.isLoading = true;
            this.error = null;
            try {
                const updatedNote = await noteService.update(id, noteData);
                const index = this.notes.findIndex(note => note.id === id);
                if (index !== -1) {
                    this.notes[index] = updatedNote;
                }
                return updatedNote;
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to update note';
                console.error('Error updating note:', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Удалить заметку
        async deleteNote(id: number) {
            this.isLoading = true;
            this.error = null;
            try {
                await noteService.delete(id);
                this.notes = this.notes.filter(note => note.id !== id);
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to delete note';
                console.error('Error deleting note:', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Установить текущую заметку для редактирования
        setCurrentNote(note: Note | null) {
            this.currentNote = note;
        },

        // Сбросить текущую заметку
        resetCurrentNote() {
            this.currentNote = null;
        },
    },

    getters: {
        // Получить заметку по ID
        getNoteById: (state) => (id: number) => {
            return state.notes.find(note => note.id === id);
        },

        // Получить отсортированные по дате заметки
        sortedNotes: (state) => {
            return [...state.notes].sort((a, b) => {
                return new Date(b.updatedAt || '').getTime() - new Date(a.updatedAt || '').getTime();
            });
        },
    },
});