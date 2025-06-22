import { useState, useEffect } from 'react';
import { useAuthStore } from '@/stores/useAuthStore';

export const useAuth = () => {
  const {
    token,
    user,
    isLoading,
    isAuthenticated,
    login,
    logout,
    checkAuth,
  } = useAuthStore();

  useEffect(() => {
    if (token && !isAuthenticated && isLoading) {
      checkAuth();
    } else if (!token && isLoading) {
      useAuthStore.setState({ isLoading: false });
    }
  }, [token, isAuthenticated, isLoading, checkAuth]);

  return {
    user,
    isLoading,
    isAuthenticated,
    login,
    logout,
    checkAuth,
  };
};

interface ApiOptions extends RequestInit {
  headers?: Record<string, string>;
}

interface ApiResponse<T> {
  data: T | null;
  loading: boolean;
  error: string | null;
  refetch: () => Promise<void>;
}

export const useApi = <T>(endpoint: string, options: ApiOptions = {}): ApiResponse<T> => {
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  useEffect(() => {
    const abortController = new AbortController();

    const fetchData = async () => {
      try {
        if (!import.meta.env.VITE_API_URL) {
          throw new Error('API URL is not configured');
        }

        setLoading(true);
        setError(null);
        
        const token = localStorage.getItem('authToken');
        const headers = {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          ...(options.headers || {}),
        };

        const response = await fetch(`${import.meta.env.VITE_API_URL}${endpoint}`, {
          ...options,
          headers,
          signal: abortController.signal,
        });

        if (!response.ok) {
          const errorData = await response.json().catch(() => null);
          throw new Error(
            errorData?.message || `HTTP error! status: ${response.status}`
          );
        }

        const result = await response.json();
        setData(result);
      } catch (err) {
        if (err instanceof Error) {
          setError(err.message);
        } else {
          setError('An unknown error occurred');
        }
      } finally {
        setLoading(false);
      }
    };

    fetchData();

    return () => {
      abortController.abort();
    };
  }, [endpoint, JSON.stringify(options)]);

  const refetch = async () => {
    const abortController = new AbortController();
    
    try {
      if (!import.meta.env.VITE_API_URL) {
        throw new Error('API URL is not configured');
      }

      setLoading(true);
      setError(null);
      
      const token = localStorage.getItem('authToken');
      const headers = {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        ...(options.headers || {}),
      };

      const response = await fetch(`${import.meta.env.VITE_API_URL}${endpoint}`, {
        ...options,
        headers,
        signal: abortController.signal,
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => null);
        throw new Error(
          errorData?.message || `HTTP error! status: ${response.status}`
        );
      }

      const result = await response.json();
      setData(result);
    } catch (err) {
      if (err instanceof Error) {
        setError(err.message);
      } else {
        setError('An unknown error occurred');
      }
    } finally {
      setLoading(false);
    }
  };

  return { data, loading, error, refetch };
};
