import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  withCredentials: true
})

export async function csrf() {
  await api.get('/sanctum/csrf-cookie')
}

export default api
