import axios from 'axios';

// Use relative URLs when running in development with proxy
const API_URL = '/api';

// Create a separate instance for CSRF cookie
const csrfInstance = axios.create({
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
});

const axiosInstance = axios.create({
  baseURL: API_URL,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});

// Add a request interceptor to add the token
axiosInstance.interceptors.request.use(
  async (config) => {
    // Get CSRF cookie before making any request that's not the CSRF request itself
    if (!document.cookie.includes('XSRF-TOKEN') && !config.url?.includes('sanctum/csrf-cookie')) {
      await csrfInstance.get('/sanctum/csrf-cookie');
    }

    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Add a response interceptor to handle errors
axiosInstance.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    // Handle token expiration and refresh
    if (error.response?.status === 401) {
      const originalRequest = error.config;
      
      // Prevent infinite refresh loops
      if (!originalRequest._retry && !originalRequest.url?.includes('auth/admin/refresh')) {
        originalRequest._retry = true;

        try {
          // Try to refresh the token
          const response = await axiosInstance.post('/api/v1/auth/admin/refresh');
          const { token } = response.data.data;

          localStorage.setItem('token', token);

          // Update the authorization header
          originalRequest.headers.Authorization = `Bearer ${token}`;
          
          // Retry the original request
          return axiosInstance(originalRequest);
        } catch (refreshError) {
          // If refreshing fails, clear auth state and redirect to login
          localStorage.removeItem('token');
          
          // Only redirect to login if we're not already there
          if (!window.location.pathname.includes('/login')) {
            window.location.href = '/login';
          }
        }
      }
    }

    // Handle other errors
    if (error.response?.status === 403) {
      console.error('Access forbidden:', error.response.data.message);
    } else if (error.response?.status === 422) {
      console.error('Validation error:', error.response.data.errors);
    } else if (error.response?.status === 429) {
      console.error('Too many requests. Please try again later.');
    }

    return Promise.reject(error);
  }
);

export default axiosInstance;
