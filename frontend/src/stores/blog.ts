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
  isSearchMode: boolean;
  hasSearched: boolean;
  searchPage: number;
  searchLimit: number;
  searchParams: {
    q?: string;
    tags?: string[];
    author_id?: number;
    from?: string;
    to?: string;
  };
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
    isSearchMode: false,
    hasSearched: false,
    searchPage: 1,
    searchLimit: 10,
    searchParams: {},
  }),
  actions: {
    async fetchPosts() {
      this.isLoading = true;
      this.errorMessage = '';
      this.nextCursor = null;
      this.hasMore = true;
      this.isSearchMode = false;
      this.hasSearched = false;

      try {
        const response = await api.get<CursorPagination<Post>>('/posts');
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

    async searchPosts(params: {
      q?: string;
      tags?: string[];
      author_id?: number;
      from?: string;
      to?: string;
      limit?: number;
    }) {
      this.isLoading = true;
      this.errorMessage = '';
      this.isSearchMode = true;
      this.hasSearched = true;
      this.searchPage = 1;
      this.searchLimit = params.limit ?? 10;
      this.searchParams = {
        q: params.q,
        tags: params.tags,
        author_id: params.author_id,
        from: params.from,
        to: params.to,
      };
      this.isLoadingMore = false;
      this.hasMore = true;

      try {
        const response = await api.get<Post[]>('/posts/search', {
          params: {
            ...this.searchParams,
            page: this.searchPage,
            limit: this.searchLimit,
          },
        });
        const posts = response.data ?? [];
        this.posts = posts;
        this.hasMore = posts.length >= this.searchLimit;
      } catch (error) {
        console.error(error);
        this.errorMessage = 'Unable to search posts right now. Please try again later.';
      } finally {
        this.isLoading = false;
      }
    },
    async fetchMoreSearchPosts() {
      if (!this.isSearchMode || !this.hasMore || this.isLoadingMore) return;

      this.isLoadingMore = true;
      this.errorMessage = '';

      try {
        const nextPage = this.searchPage + 1;
        const response = await api.get<Post[]>('/posts/search', {
          params: {
            ...this.searchParams,
            page: nextPage,
            limit: this.searchLimit,
          },
        });
        const posts = response.data ?? [];
        this.posts = [...this.posts, ...posts];
        this.searchPage = nextPage;
        this.hasMore = posts.length >= this.searchLimit;
      } catch (error) {
        console.error(error);
        this.errorMessage = 'Unable to load more search results right now. Please try again later.';
      } finally {
        this.isLoadingMore = false;
      }
    },
    async resetSearch() {
      this.isSearchMode = false;
      this.hasSearched = false;
      this.searchPage = 1;
      this.searchLimit = 10;
      this.searchParams = {};
      this.posts = [];
      this.hasMore = true;
      await this.fetchPosts();
    },

    async fetchMorePosts() {
      if (this.isSearchMode || !this.hasMore || this.isLoadingMore) return;

      this.isLoadingMore = true;
      this.errorMessage = '';

      try {
        const response = await api.get<CursorPagination<Post>>('/posts', {
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
        const response = await api.get<Post>(`/posts/${id}`);
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
