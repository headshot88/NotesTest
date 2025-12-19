// src/services/api.ts
import axios from 'axios';
import type { Note } from '@/types/note';

const API_BASE_URL = 'http://localhost:8000/api';

const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
    },
});

// Перехватчик для обработки ошибок
api.interceptors.response.use(
    (response) => response,
    (error) => {
        console.error('API Error:', error.response?.data || error.message);
        return Promise.reject(error);
    }
);

export const noteService = {
    // Получить все заметки
    async getAll(): Promise<Note[]> {
        const response = await api.get('/notes');
        return response.data;
    },

    // Получить одну заметку
    async getOne(id: number): Promise<Note> {
        const response = await api.get(`/notes/${id}`);
        return response.data;
    },

    // Создать заметку
    async create(note: Omit<Note, 'id'>): Promise<Note> {
        const response = await api.post('/notes', note);
        return response.data;
    },

    // Обновить заметку
    async update(id: number, note: Partial<Note>): Promise<Note> {
        const response = await api.put(`/notes/${id}`, note);
        return response.data;
    },

    // Удалить заметку
    async delete(id: number): Promise<void> {
        await api.delete(`/notes/${id}`);
    }
};