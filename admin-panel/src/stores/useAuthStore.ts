import { create } from 'zustand';
import axios from '@/lib/axios';
import axiosBase from 'axios';

// Create a separate axios instance for CSRF cookie
const csrfAxios = axiosBase.create({
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
});

interface AuthState {
  token: string | null;
  user: any | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  checkAuth: () => Promise<void>;
}

export const useAuthStore = create<AuthState>((set) => ({
  token: localStorage.getItem('token'),
  user: null,
  isLoading: true,
  isAuthenticated: false,

  login: async (email: string, password: string) => {
    try {
      // Get CSRF token first
      await csrfAxios.get('/sanctum/csrf-cookie');
      
      const response = await axios.post('/v1/auth/admin/login', {
        email,
        password,
      });

      const { token, admin } = response.data.data;
      localStorage.setItem('token', token);

      set({
        token,
        user: admin,
        isAuthenticated: true,
        isLoading: false,
      });
    } catch (error) {
      localStorage.removeItem('token');
      set({
        token: null,
        user: null,
        isAuthenticated: false,
        isLoading: false,
      });
      throw error;
    }
  },

  logout: async () => {
    try {
      await axios.post('/v1/auth/admin/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      localStorage.removeItem('token');
      set({
        token: null,
        user: null,
        isAuthenticated: false,
        isLoading: false,
      });
    }
  },

  checkAuth: async () => {
    try {
      const response = await axios.get('/v1/auth/admin/profile');
      const { admin } = response.data.data;

      set({
        user: admin,
        isAuthenticated: true,
        isLoading: false,
      });
    } catch (error) {
      localStorage.removeItem('token');
      set({
        token: null,
        user: null,
        isAuthenticated: false,
        isLoading: false,
      });
      throw error;
    }
  },
}));
