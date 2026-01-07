import { defineStore } from 'pinia';
import api from '../services/api';
import type { CursorPagination, Post } from '../@types/blog';

interface BlogState {
  posts: Post[];
  isLoading: boolean;
  errorMessage: string;
  selectedPost: Post | null;
  isDetailLoading: boolean;
  detailErrorMessage: string;
  nextCursor: string | null;
  isLoadingMore: boolean;
  hasMore: boolean;
}

export const useBlogStore = defineStore('blog', {
  state: (): BlogState => ({
    posts: [],
    isLoading: false,
    errorMessage: '',
    selectedPost: null,
    isDetailLoading: false,
    detailErrorMessage: '',
    nextCursor: null,
    isLoadingMore: false,
    hasMore: true,
  }),
  actions: {
    async fetchPosts() {
      this.isLoading = true;
      this.errorMessage = '';
      this.nextCursor = null;
      this.hasMore = true;

      try {
        const response = await api.get<CursorPagination<Post>>('/v1/posts');
        const posts = response.data.data ?? [];
        this.posts = posts.filter((post) => !post.status || post.status === 'published');
        this.nextCursor = response.data.next_cursor ?? null;
        this.hasMore = Boolean(this.nextCursor);
      } catch (error) {
        console.error(error);
        this.errorMessage = 'Unable to load posts right now. Please try again later.';
      } finally {
        this.isLoading = false;
      }
    },
    async fetchMorePosts() {
      if (!this.hasMore || this.isLoadingMore) return;

      this.isLoadingMore = true;
      this.errorMessage = '';

      try {
        const response = await api.get<CursorPagination<Post>>('/v1/posts', {
          params: {
            cursor: this.nextCursor,
          },
        });
        const posts = response.data.data ?? [];
        const publishedPosts = posts.filter((post) => !post.status || post.status === 'published');
        this.posts = [...this.posts, ...publishedPosts];
        this.nextCursor = response.data.next_cursor ?? null;
        this.hasMore = Boolean(this.nextCursor);
      } catch (error) {
        console.error(error);
        this.errorMessage = 'Unable to load more posts right now. Please try again later.';
      } finally {
        this.isLoadingMore = false;
      }
    },
    async fetchPost(id: string | number) {
      if (!id) return;
      this.isDetailLoading = true;
      this.detailErrorMessage = '';

      try {
        const response = await api.get<Post>(`/v1/posts/${id}`);
        this.selectedPost = response.data;
      } catch (error) {
        console.error(error);
        this.detailErrorMessage = 'Unable to load this post. It might be unpublished or unavailable.';
      } finally {
        this.isDetailLoading = false;
      }
    },
    clearSelectedPost() {
      this.selectedPost = null;
      this.detailErrorMessage = '';
    },
  },
});
