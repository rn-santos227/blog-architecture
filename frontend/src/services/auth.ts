import type { User } from '@/@types/user'
import api, { csrf } from './api'

export interface AuthPayload {
  user: User
  token: string
}

export async function loginUser(email: string, password: string) {
  await csrf()
  const response = await api.post<AuthPayload>('/auth/login', {
    email,
    password,
  })

  return response.data
}

export async function registerUser(name: string, email: string, password: string) {
  await csrf()
  const response = await api.post<AuthPayload>('/auth/register', {
    name,
    email,
    password,
  })

  return response.data
}
