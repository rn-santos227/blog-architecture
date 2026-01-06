import { z } from 'zod'

export const searchPostSchema = z.object({
  q: z.string().min(2),
  page: z.number().int().min(1).optional(),
  limit: z.number().int().min(1).max(50).optional(),
  author_id: z.number().int().optional(),
  tag: z.string().min(2).optional(),
  from: z.string().datetime().optional(),
  to: z.string().datetime().optional()
})

export type SearchPostInput = z.infer<typeof searchPostSchema>
