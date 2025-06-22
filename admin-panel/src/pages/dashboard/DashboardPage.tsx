import React, { useState, useEffect } from 'react';
import {
  Box,
  Grid,
  Paper,
  Typography,
  Card,
  CardContent,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Chip,
  IconButton,
  Button,
  Stack,
  Skeleton
} from '@mui/material';
import {
  TrendingUp,
  TrendingDown,
  People,
  ShoppingCart,
  Inventory,
  CurrencyExchange,
  Visibility,
} from '@mui/icons-material';
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from 'recharts';

import { useStore } from '../../stores/useStore';

// Type definitions
interface StatCardProps {
  title: string;
  value: string | number | JSX.Element;
  icon: React.ReactNode;
  trend: number;
  color: string;
}

interface Session {
  id: string | number;
  title: string;
  viewers: number;
  duration: string;
  status: string;
}

interface Order {
  id: string | number;
  customer: string;
  total: number;
  status: string;
  date: string;
}

interface RevenueChartData {
  month: string;
  revenue: number;
}

interface DashboardStats {
  totalCustomers?: number;
  customerGrowth?: number;
  totalOrders?: number;
  orderGrowth?: number;
  totalProducts?: number;
  productGrowth?: number;
  totalRevenue?: number;
  revenueGrowth?: number;
  revenueChart?: RevenueChartData[];
  recentOrders?: Order[];
}

interface LiveStreamStats {
  isLive?: boolean;
  viewers?: number;
  duration?: string;
  sessions?: Session[];
}

const StatCard: React.FC<StatCardProps> = ({ title, value, icon, trend, color }) => (
  <Card>
    <CardContent>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 2 }}>
        <Box sx={{ color }}>{icon}</Box>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
          {trend > 0 ? (
            <TrendingUp sx={{ color: 'success.main' }} />
          ) : (
            <TrendingDown sx={{ color: 'error.main' }} />
          )}
          <Typography variant="body2" color={trend > 0 ? 'success.main' : 'error.main'}>
            {Math.abs(trend)}%
          </Typography>
        </Box>
      </Box>
      <Typography variant="h4" sx={{ mb: 1 }}>
        {typeof value === 'number' ? value.toLocaleString() : value}
      </Typography>
      <Typography variant="body2" color="text.secondary">
        {title}
      </Typography>
    </CardContent>
  </Card>
);

const DashboardPage: React.FC = () => {
  const {
    dashboardStats,
    loadingDashboard,
    getDashboardStats,
    liveStreamStats,
    loadingLiveStream,
    getLiveStreamStats,
  } = useStore() as {
    dashboardStats: DashboardStats | null;
    loadingDashboard: boolean;
    getDashboardStats: () => void;
    liveStreamStats: LiveStreamStats | null;
    loadingLiveStream: boolean;
    getLiveStreamStats: () => void;
  };

  useEffect(() => {
    getDashboardStats();
    getLiveStreamStats();

    const interval = setInterval(() => {
      getDashboardStats();
      getLiveStreamStats();
    }, 30000);

    return () => clearInterval(interval);
  }, [getDashboardStats, getLiveStreamStats]);

  const getStatusColor = (status: string): "success" | "warning" | "error" | "info" => {
    switch (status.toLowerCase()) {
      case 'completed':
        return 'success';
      case 'pending':
        return 'warning';
      case 'active':
        return 'info';
      default:
        return 'error';
    }
  };

  const handleStartNewLive = (): void => {
    // Implement start new live functionality
    console.log('Starting new live session...');
  };

  const handleViewOrder = (orderId: string | number): void => {
    // Implement view order functionality
    console.log('Viewing order:', orderId);
  };

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 4 }}>
        Dashboard
      </Typography>

      {/* Stat Cards */}
      <Grid container spacing={3} sx={{ mb: 4 }}>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            title="Total Users"
            value={
              loadingDashboard
                ? <Skeleton width={100} />
                : (dashboardStats?.totalCustomers?.toLocaleString() || '0')
            }
            icon={<People sx={{ fontSize: 40 }} />}
            trend={dashboardStats?.customerGrowth || 0}
            color="#1976d2"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            title="Total Orders"
            value={
              loadingDashboard
                ? <Skeleton width={100} />
                : (dashboardStats?.totalOrders?.toLocaleString() || '0')
            }
            icon={<ShoppingCart sx={{ fontSize: 40 }} />}
            trend={dashboardStats?.orderGrowth || 0}
            color="#2e7d32"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            title="Total Products"
            value={
              loadingDashboard
                ? <Skeleton width={100} />
                : (dashboardStats?.totalProducts?.toLocaleString() || '0')
            }
            icon={<Inventory sx={{ fontSize: 40 }} />}
            trend={dashboardStats?.productGrowth || 0}
            color="#ed6c02"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            title="Total Revenue"
            value={
              loadingDashboard
                ? <Skeleton width={100} />
                : `Rp ${(dashboardStats?.totalRevenue || 0).toLocaleString()}`
            }
            icon={<CurrencyExchange sx={{ fontSize: 40 }} />}
            trend={dashboardStats?.revenueGrowth || 0}
            color="#9c27b0"
          />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        {/* Revenue Chart */}
        <Grid item xs={12} md={8}>
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" sx={{ mb: 3 }}>
              Monthly Revenue
            </Typography>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={dashboardStats?.revenueChart || []}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip 
                  formatter={(value: number) => [`Rp ${value.toLocaleString()}`, 'Revenue']}
                />
                <Line 
                  type="monotone" 
                  dataKey="revenue" 
                  stroke="#000" 
                  strokeWidth={2}
                  dot={{ fill: '#000' }}
                />
              </LineChart>
            </ResponsiveContainer>
          </Paper>
        </Grid>

        {/* Live Sessions */}
        <Grid item xs={12} md={4}>
          <Paper sx={{ p: 3, height: '100%' }}>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 3 }}>
              <Typography variant="h6">Live Sessions</Typography>
              <Button
                variant="contained"
                size="small"
                onClick={handleStartNewLive}
                sx={{
                  backgroundColor: 'black',
                  '&:hover': {
                    backgroundColor: '#333',
                  },
                }}
              >
                Start New Live
              </Button>
            </Box>

            {loadingLiveStream ? (
              <Skeleton variant="rectangular" height={100} />
            ) : liveStreamStats?.isLive ? (
              <Card sx={{ mb: 2 }}>
                <CardContent>
                  <Typography variant="subtitle1">Current Live Session</Typography>
                  <Stack direction="row" spacing={2} alignItems="center" sx={{ mt: 1 }}>
                    <Chip 
                      size="small" 
                      label={`${liveStreamStats.viewers || 0} viewers`} 
                      color="primary" 
                    />
                    <Typography variant="body2" color="text.secondary">
                      {liveStreamStats.duration || '00:00'}
                    </Typography>
                    <Chip size="small" label="Live" color="error" />
                  </Stack>
                </CardContent>
              </Card>
            ) : (
              <Typography color="text.secondary">No active live sessions</Typography>
            )}

            {/* SESSIONS LIST */}
            {liveStreamStats?.sessions && liveStreamStats.sessions.length > 0 && 
              liveStreamStats.sessions.map((session: Session) => (
                <Card key={session.id} sx={{ mb: 2 }}>
                  <CardContent>
                    <Typography variant="subtitle1">{session.title}</Typography>
                    <Stack direction="row" spacing={2} alignItems="center" sx={{ mt: 1 }}>
                      <Chip 
                        size="small" 
                        label={`${session.viewers} viewers`} 
                        color="primary" 
                      />
                      <Typography variant="body2" color="text.secondary">
                        {session.duration}
                      </Typography>
                      <Chip 
                        size="small" 
                        label={session.status} 
                        color={getStatusColor(session.status)} 
                      />
                    </Stack>
                  </CardContent>
                </Card>
              ))
            }
          </Paper>
        </Grid>

        {/* Recent Orders */}
        <Grid item xs={12}>
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" sx={{ mb: 3 }}>
              Recent Orders
            </Typography>
            <TableContainer>
              <Table>
                <TableHead>
                  <TableRow>
                    <TableCell>Order ID</TableCell>
                    <TableCell>Customer</TableCell>
                    <TableCell>Total</TableCell>
                    <TableCell>Status</TableCell>
                    <TableCell>Date</TableCell>
                    <TableCell align="right">Action</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {loadingDashboard ? (
                    [...Array(3)].map((_, index) => (
                      <TableRow key={index}>
                        {[...Array(6)].map((__, i) => (
                          <TableCell key={i}>
                            <Skeleton />
                          </TableCell>
                        ))}
                      </TableRow>
                    ))
                  ) : dashboardStats?.recentOrders && dashboardStats.recentOrders.length > 0 ? (
                    dashboardStats.recentOrders.map((order: Order) => (
                      <TableRow key={order.id} hover>
                        <TableCell>#{order.id}</TableCell>
                        <TableCell>{order.customer}</TableCell>
                        <TableCell>Rp {order.total.toLocaleString()}</TableCell>
                        <TableCell>
                          <Chip
                            size="small"
                            label={order.status}
                            color={getStatusColor(order.status)}
                          />
                        </TableCell>
                        <TableCell>{order.date}</TableCell>
                        <TableCell align="right">
                          <IconButton 
                            size="small" 
                            sx={{ color: 'black' }}
                            onClick={() => handleViewOrder(order.id)}
                            aria-label={`View order ${order.id}`}
                          >
                            <Visibility />
                          </IconButton>
                        </TableCell>
                      </TableRow>
                    ))
                  ) : (
                    <TableRow>
                      <TableCell colSpan={6} align="center">
                        <Typography color="text.secondary">
                          No recent orders found
                        </Typography>
                      </TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
            </TableContainer>
          </Paper>
        </Grid>
      </Grid>
    </Box>
  );
};

export default DashboardPage;  // Default export