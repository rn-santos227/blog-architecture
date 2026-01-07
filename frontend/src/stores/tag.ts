import { defineStore } from 'pinia';
import api from '@/services/api';
import type { Tag } from '@/@types/blog';

interface TagState {
  tags: Tag[];
  isLoading: boolean;
  errorMessage: string;
}

export const useTagsStore = defineStore('tags', {
  state: (): TagState => ({
    tags: [],
    isLoading: false,
    errorMessage: '',
  }),
  actions: {
    async fetchTags(limit = 50) {
      if (this.isLoading) return;
      this.isLoading = true;
      this.errorMessage = '';

      try {
        const response = await api.get<Tag[]>('/tags', {
          params: {
            limit,
          },
        });
        this.tags = response.data ?? [];
      } catch (error) {
        console.error(error);
        this.errorMessage = 'Unable to load tags right now. Please try again later.';
      } finally {
        this.isLoading = false;
      }
    },
  },
});
