import { createBrowserRouter, Navigate } from 'react-router-dom';
import { Layout } from '@/components/layout/Layout';
import { AuthGuard } from '@/components/auth/AuthGuard';
import { LoginPage } from '@/pages/auth/LoginPage';
import { OrderComplaintsPage } from '@/pages/orders';
import DashboardPage from '@/pages/dashboard/DashboardPage';
import { LiveDashboardPage } from '@/pages/streaming/LiveDashboardPage';
import { LiveVouchersPage } from '@/pages/streaming/LiveVouchersPage';
import { LiveOrdersPage } from '@/pages/streaming/LiveOrdersPage';
import { LiveStreamingPage } from '@/pages/streaming/LiveStreamingPage';
import { LiveCommentsPage } from '@/pages/streaming/LiveCommentsPage';
import { LiveStreamTablePage } from '@/pages/streaming/LiveStreamTablePage';
import { ProductsPage } from '@/pages/ecommerce/ProductsPage';
import CategoriesPage from '@/pages/ecommerce/CategoriesPage';
import CategoryForm from '@/pages/ecommerce/CategoryForm';
import { ProductForm } from '@/pages/ecommerce/ProductForm';
import { OrdersPage } from '@/pages/ecommerce/OrdersPage';
import { PaymentsPage } from '@/pages/ecommerce/PaymentsPage';
import { PaymentProofsPage } from '@/pages/ecommerce/PaymentProofsPage';
import { PaymentSettingsPage } from '@/pages/ecommerce/PaymentSettingsPage';
import { ShippingPage } from '@/pages/ecommerce/ShippingPage';
import { UsersPage } from '@/pages/users';
import { AdminsPage } from '@/pages/management';
import { SettingsPage } from '@/pages/settings';
import { OrderList, OrderDetail } from '@/pages/orders';
import MessagesPage from '@/pages/whatsapp/MessagesPage';
import AutoRepliesPage from '@/pages/whatsapp/AutoRepliesPage';

// Configure router with future flags
export const router = createBrowserRouter(
  [
  {
    path: '/login',
    element: <LoginPage />,
  },
  {
    path: '/',
    element: (
      <AuthGuard>
        <Layout />
      </AuthGuard>
    ),
    children: [
      {
        index: true,
        element: <Navigate to="/dashboard" replace />,
      },
      {
        path: 'dashboard',
        element: <DashboardPage />,
      },
      {
        path: 'live',
        children: [
          {
            index: true,
            element: <LiveDashboardPage />,
          },
          {
            path: 'vouchers',
            element: <LiveVouchersPage />,
          },
          {
            path: 'orders',
            element: <LiveOrdersPage />,
          },
        ],
      },
      {
        path: 'streaming',
        children: [
          {
            path: 'dashboard',
            element: <LiveDashboardPage />,
          },
          {
            path: 'table',
            element: <LiveStreamTablePage />,
          },
          {
            path: 'live',
            element: <LiveStreamingPage />,
          },
          {
            path: 'vouchers',
            element: <LiveVouchersPage />,
          },
          {
            path: 'orders',
            element: <LiveOrdersPage />,
          },
          {
            path: 'comments',
            element: <LiveCommentsPage />,
          },
        ],
      },
      {
        path: 'products',
        children: [
          {
            index: true,
            element: <ProductsPage />,
          },
          {
            path: 'create',
            element: <ProductForm />,
          },
          {
            path: ':id/edit',
            element: <ProductForm />,
          },
        ],
      },
      {
        path: 'categories',
        children: [
          {
            index: true,
            element: <CategoriesPage />,
          },
          {
            path: 'create',
            element: <CategoryForm />,
          },
          {
            path: ':id/edit',
            element: <CategoryForm />,
          },
        ],
      },
      {
        path: 'orders',
        children: [
          {
            index: true,
            element: <OrderList />,
          },
          {
            path: ':id',
            element: <OrderDetail />,
          },
          {
            path: 'complaints',
            element: <OrderComplaintsPage />,
          },
        ],
      },
      {
        path: 'ecommerce-orders',
        element: <OrdersPage />,
      },
      {
        path: 'payments',
        element: <PaymentsPage />,
      },
      {
        path: 'payment-proofs',
        element: <PaymentProofsPage />,
      },
      {
        path: 'payment-settings',
        element: <PaymentSettingsPage />,
      },
      {
        path: 'shipping',
        element: <ShippingPage />,
      },
      {
        path: 'users',
        children: [
          {
            index: true,
            element: <UsersPage />,
          },
        ],
      },
      {
        path: 'admins',
        children: [
          {
            index: true,
            element: <AdminsPage />,
          },
        ],
      },
      {
        path: 'settings',
        element: <SettingsPage />,
      },
      {
        path: 'whatsapp',
        children: [
          {
            index: true,
            element: <MessagesPage />,
          },
          {
            path: 'auto-replies',
            element: <AutoRepliesPage />,
          },
        ],
      },
    ],
  },
  {
    path: '*',
    element: <Navigate to="/dashboard" replace />,
  },
  ],
  {
    future: {
      v7_normalizeFormMethod: true,
      v7_partialHydration: true,
      v7_skipActionErrorRevalidation: true,
    }
  }
);
