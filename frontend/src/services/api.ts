import axios from 'axios'

const api = axios.create({
  baseURL: `${import.meta.env.VITE_API_BASE_URL}/api/v1`,
  withCredentials: true,
})

export async function csrf() {
  await axios.get(`${import.meta.env.VITE_API_BASE_URL}/sanctum/csrf-cookie`, {
    withCredentials: true,
  })
}

export function setAuthToken(token: string | null) {
  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`
  } else {
    delete api.defaults.headers.common.Authorization
  }
}

export default api
