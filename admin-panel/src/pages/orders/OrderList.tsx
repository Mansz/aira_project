import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  Box,
  Paper,
  Typography,
  Grid,
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
  TextField,
  InputAdornment,
  Stack,
  Button,
  Menu,
  MenuItem,
} from '@mui/material';
import {
  Search,
  Visibility,
  ShoppingCart,
  LocalShipping,
  AttachMoney,
  Timeline,
  FilterList,
} from '@mui/icons-material';
import { useNavigate } from 'react-router-dom';

import { api } from '@/lib/api';
import { Order } from '@/types/order';
import { useQuery } from '@tanstack/react-query';
import { toast } from 'sonner';

// Fetch orders from API
const useOrders = () => {
  return useQuery({
    queryKey: ['orders'],
    queryFn: async () => {
      try {
        const response = await api.getOrders();
        return response.data;
      } catch (error) {
        toast.error('Failed to fetch orders');
        return [];
      }
    }
  });
};

export const OrderList = () => {
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState('');
  const [filterAnchorEl, setFilterAnchorEl] = useState<null | HTMLElement>(null);
  const [selectedStatus, setSelectedStatus] = useState('all');
  
  const { data: orders = [], isLoading } = useOrders();

  // Stats calculation
  const stats = {
    totalOrders: orders.length,
    totalRevenue: orders.reduce((sum, order) => sum + Number(order.total_amount), 0),
    pendingOrders: orders.filter(order => order.status === 'Menunggu Pembayaran' || order.status === 'Menunggu Konfirmasi').length,
    shippedOrders: orders.filter(order => order.status === 'Dikirim').length,
  };

  const handleFilterClick = (event: React.MouseEvent<HTMLElement>) => {
    setFilterAnchorEl(event.currentTarget);
  };

  const handleFilterClose = () => {
    setFilterAnchorEl(null);
  };

  const handleStatusFilter = (status: string) => {
    setSelectedStatus(status);
    handleFilterClose();
  };

  const getStatusColor = (status: string): "success" | "warning" | "error" | "info" => {
    switch (status) {
      case 'Selesai':
        return 'success';
      case 'Dikirim':
        return 'info';
      case 'Diproses':
        return 'warning';
      case 'Menunggu Pembayaran':
      case 'Menunggu Konfirmasi':
        return 'error';
      case 'Dibatalkan':
        return 'error';
      default:
        return 'warning';
    }
  };

  const filteredOrders = orders.filter((order) => {
    const matchesSearch =
      order.id.toString().toLowerCase().includes(searchQuery.toLowerCase()) ||
      (order.user?.name || '').toLowerCase().includes(searchQuery.toLowerCase());
    const matchesStatus =
      selectedStatus === 'all' || order.status === selectedStatus;
    return matchesSearch && matchesStatus;
  });

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 4 }}>
        Orders
      </Typography>

      <Grid container spacing={3} sx={{ mb: 4 }}>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <ShoppingCart sx={{ fontSize: 40, color: 'primary.main' }} />
                <Typography variant="h4">{stats.totalOrders}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Total Orders
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <AttachMoney sx={{ fontSize: 40, color: 'success.main' }} />
                <Typography variant="h4">
                  Rp {stats.totalRevenue.toLocaleString()}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Total Revenue
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <Timeline sx={{ fontSize: 40, color: 'warning.main' }} />
                <Typography variant="h4">{stats.pendingOrders}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Pending Orders
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <LocalShipping sx={{ fontSize: 40, color: 'info.main' }} />
                <Typography variant="h4">{stats.shippedOrders}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Shipped Orders
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      <Paper sx={{ p: 3 }}>
        <Box sx={{ mb: 3, display: 'flex', gap: 2 }}>
          <TextField
            fullWidth
            placeholder="Search orders..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Search />
                </InputAdornment>
              ),
            }}
          />
          <Button
            startIcon={<FilterList />}
            onClick={handleFilterClick}
            sx={{ color: 'black' }}
          >
            Filter
          </Button>
        </Box>

        <TableContainer>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Order ID</TableCell>
                <TableCell>Customer</TableCell>
                <TableCell>Total</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Payment</TableCell>
                <TableCell>Shipping</TableCell>
                <TableCell>Date</TableCell>
                <TableCell align="right">Actions</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {filteredOrders.map((order) => (
                <TableRow key={order.id}>
                  <TableCell>#{order.id}</TableCell>
                  <TableCell>
                    <Stack>
                      <Typography variant="body2">{order.user?.name || 'Unknown'}</Typography>
                      <Typography variant="caption" color="text.secondary">
                        {order.user?.email || 'No email'}
                      </Typography>
                    </Stack>
                  </TableCell>
                  <TableCell>Rp {Number(order.total_amount).toLocaleString()}</TableCell>
                  <TableCell>
                    <Chip
                      size="small"
                      label={order.status}
                      color={getStatusColor(order.status)}
                    />
                  </TableCell>
                  <TableCell>
                    <Chip
                      size="small"
                      label={order.payment_method || 'Belum Bayar'}
                      color={order.payment_proof?.verified_at ? 'success' : 'error'}
                    />
                  </TableCell>
                  <TableCell>
                    <Chip
                      size="small"
                      label={order.shipping_courier || 'Belum Dikirim'}
                      color={order.tracking_number ? 'success' : 'warning'}
                    />
                  </TableCell>
                  <TableCell>
                    {new Date(order.created_at).toLocaleDateString('id-ID')}
                  </TableCell>
                  <TableCell align="right">
                    <IconButton
                      size="small"
                      onClick={() => navigate(`/orders/${order.id}`)}
                      sx={{ color: 'black' }}
                    >
                      <Visibility />
                    </IconButton>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </TableContainer>
      </Paper>

      <Menu
        anchorEl={filterAnchorEl}
        open={Boolean(filterAnchorEl)}
        onClose={handleFilterClose}
      >
        <MenuItem onClick={() => handleStatusFilter('all')}>
          Semua Status
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('Menunggu Pembayaran')}>
          Menunggu Pembayaran
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('Menunggu Konfirmasi')}>
          Menunggu Konfirmasi
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('Diproses')}>
          Diproses
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('Dikirim')}>
          Dikirim
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('Selesai')}>
          Selesai
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('Dibatalkan')}>
          Dibatalkan
        </MenuItem>
      </Menu>
    </Box>
  );
};
