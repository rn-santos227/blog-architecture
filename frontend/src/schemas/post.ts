import { z } from 'zod'

export const postSchema = z.object({
  title: z.string().trim().min(1, 'Title is required').max(255, 'Title must be 255 characters or less'),
  body: z.string().trim().min(1, 'Body is required'),
  status: z.enum(['draft', 'published']),
  tags: z.array(z.string().trim().min(1, 'Tag cannot be empty')).optional(),
})

export type PostInput = z.infer<typeof postSchema>
