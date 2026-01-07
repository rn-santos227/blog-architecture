export interface Tag {
  id: number;
  name: string;
  slug: string;
}

export interface Author {
  id: number;
  name: string;
}

export interface Post {
  id: number;
  title: string;
  body: string;
  status?: string;
  published_at: string | null;
  user: Author;
  tags: Tag[];
}

export interface CursorPagination<T> {
  data: T[];
  next_cursor?: string | null;
  prev_cursor?: string | null;
}
