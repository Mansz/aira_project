import React from 'react'
import ReactDOM from 'react-dom/client'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { ThemeProvider, CssBaseline } from '@mui/material'
import { RouterProvider } from 'react-router-dom'
import { theme } from './theme'
import { router } from './router'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: false,
    },
  },
})

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <QueryClientProvider client={queryClient}>
      <ThemeProvider theme={theme}>
        <CssBaseline />
        <RouterProvider 
          router={router} 
          future={{
            v7_startTransition: true
          }}
        />
      </ThemeProvider>
    </QueryClientProvider>
  </React.StrictMode>
)
