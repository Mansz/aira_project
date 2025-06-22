import { useState, useEffect } from 'react';
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
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Menu,
  MenuItem,
  Stepper,
  Step,
  StepLabel,
} from '@mui/material';
import {
  Search,
  Visibility,
  LocalShipping,
  LocationOn,
  Timeline,
  FilterList,
  LocalMall,
  Print,
} from '@mui/icons-material';
import { api } from '@/lib/api';
import { Shipment, ShipmentStats } from '@/types/shipment';

// Shipping status steps
const shippingSteps = ['Processing', 'In Transit', 'Out for Delivery', 'Delivered'];

interface ShipmentDetailDialogProps {
  open: boolean;
  shipment: Shipment | null;
  onClose: () => void;
}

const ShipmentDetailDialog = ({ open, shipment, onClose }: ShipmentDetailDialogProps) => {
  if (!shipment) return null;

  const getStepIndex = (status: string) => {
    const statusMap: { [key: string]: number } = {
      'processing': 0,
      'in_transit': 1,
      'out_for_delivery': 2,
      'delivered': 3,
    };
    return statusMap[status] || 0;
  };

  const handlePrint = () => {
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    // Generate HTML content for the shipping label
    const labelHTML = `
      <html>
        <head>
          <title>Shipping Label - ${shipment.id}</title>
          <style>
            body {
              font-family: 'Courier New', monospace;
              margin: 0;
              padding: 20px;
              width: 400px;
              height: 600px;
              border: 2px solid #000;
              box-sizing: border-box;
            }
            .header {
              text-align: center;
              margin-bottom: 20px;
            }
            .header h1 {
              font-size: 18px;
              font-weight: bold;
              margin: 0;
            }
            .header p {
              font-size: 12px;
              margin: 5px 0;
            }
            .tracking {
              text-align: center;
              margin: 20px 0;
            }
            .tracking h2 {
              font-size: 24px;
              font-weight: bold;
              letter-spacing: 2px;
              margin: 0;
            }
            .section {
              margin: 15px 0;
            }
            .section-title {
              font-weight: bold;
              font-size: 12px;
              margin-bottom: 5px;
            }
            .section-content {
              font-size: 12px;
              line-height: 1.4;
            }
            .divider {
              border-top: 1px solid #000;
              margin: 15px 0;
            }
            .footer {
              position: absolute;
              bottom: 20px;
              left: 20px;
              right: 20px;
              font-size: 10px;
              text-align: center;
            }
            @media print {
              body { margin: 0; }
              @page { size: A6; margin: 0; }
            }
          </style>
        </head>
        <body>
          <div class="header">
            <h1>SHIPPING LABEL</h1>
            <p>${shipment.courier.name} - ${shipment.courier.service}</p>
          </div>
          
          <div class="divider"></div>
          
          <div class="tracking">
            <h2>${shipment.courier.trackingNumber}</h2>
          </div>
          
          <div class="divider"></div>
          
          <div class="section">
            <div class="section-title">FROM:</div>
            <div class="section-content">
              Your Store Name<br>
              Jl. Contoh No. 123<br>
              Jakarta Pusat, DKI Jakarta 10110<br>
              Phone: +62 21 1234567
            </div>
          </div>
          
          <div class="divider"></div>
          
          <div class="section">
            <div class="section-title">TO:</div>
            <div class="section-content">
              <strong>${shipment.customer.name}</strong><br>
              ${shipment.customer.phone}<br><br>
              ${shipment.address.street}<br>
              ${shipment.address.city}, ${shipment.address.province}<br>
              ${shipment.address.postalCode}
            </div>
          </div>
          
          <div class="divider"></div>
          
          <div class="section">
            <div class="section-content">
              <strong>Order ID:</strong> ${shipment.orderId} &nbsp;&nbsp;
              <strong>Shipment ID:</strong> ${shipment.id}<br>
              <strong>Weight:</strong> ${shipment.weight || '1'} kg &nbsp;&nbsp;
              <strong>Items:</strong> ${shipment.items.length}
            </div>
          </div>
          
          <div class="divider"></div>
          
          <div class="section">
            <div class="section-title">ITEMS:</div>
            <div class="section-content">
              ${shipment.items.slice(0, 3).map(item => `• ${item.name} (x${item.quantity})`).join('<br>')}
              ${shipment.items.length > 3 ? `<br>... and ${shipment.items.length - 3} more items` : ''}
            </div>
          </div>
          
          <div class="footer">
            <div class="divider"></div>
            Generated on ${new Date().toLocaleDateString('id-ID')} at ${new Date().toLocaleTimeString('id-ID')}<br>
            Handle with care • Fragile items inside
          </div>
        </body>
      </html>
    `;

    // Write the content and print
    printWindow.document.write(labelHTML);
    printWindow.document.close();
    
    printWindow.onload = () => {
      printWindow.print();
      printWindow.onafterprint = () => {
        printWindow.close();
      };
    };
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        Detail Pengiriman {shipment.id}
      </DialogTitle>
      <DialogContent>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Stepper activeStep={getStepIndex(shipment.status)} sx={{ mb: 4 }}>
              {shippingSteps.map((label) => (
                <Step key={label}>
                  <StepLabel>{label}</StepLabel>
                </Step>
              ))}
            </Stepper>
          </Grid>

          <Grid item xs={12} md={6}>
            <Card variant="outlined">
              <CardContent>
                <Typography variant="subtitle2" color="text.secondary">
                  Informasi Pengiriman
                </Typography>
                <Box sx={{ mt: 2 }}>
                  <Stack spacing={2}>
                    <Box>
                      <Typography variant="body2" color="text.secondary">
                        Kurir
                      </Typography>
                      <Typography variant="body1">
                        {shipment.courier.name} - {shipment.courier.service}
                      </Typography>
                    </Box>
                    <Box>
                      <Typography variant="body2" color="text.secondary">
                        No. Resi
                      </Typography>
                      <Typography variant="body1">
                        {shipment.courier.trackingNumber}
                      </Typography>
                    </Box>
                  </Stack>
                </Box>
              </CardContent>
            </Card>
          </Grid>

          <Grid item xs={12} md={6}>
            <Card variant="outlined">
              <CardContent>
                <Typography variant="subtitle2" color="text.secondary">
                  Informasi Penerima
                </Typography>
                <Box sx={{ mt: 2 }}>
                  <Typography variant="body1">{shipment.customer.name}</Typography>
                  <Typography variant="body2">{shipment.customer.phone}</Typography>
                  <Typography variant="body2" sx={{ mt: 1 }}>
                    {shipment.address.street}
                  </Typography>
                  <Typography variant="body2">
                    {shipment.address.city}, {shipment.address.province} {shipment.address.postalCode}
                  </Typography>
                </Box>
              </CardContent>
            </Card>
          </Grid>

          <Grid item xs={12}>
            <Card variant="outlined">
              <CardContent>
                <Typography variant="subtitle2" color="text.secondary">
                  Item yang Dikirim
                </Typography>
                <TableContainer sx={{ mt: 2 }}>
                  <Table size="small">
                    <TableHead>
                      <TableRow>
                        <TableCell>Item</TableCell>
                        <TableCell align="right">Jumlah</TableCell>
                      </TableRow>
                    </TableHead>
                    <TableBody>
                      {shipment.items.map((item: { name: string; quantity: number }, index: number) => (
                        <TableRow key={index}>
                          <TableCell>{item.name}</TableCell>
                          <TableCell align="right">{item.quantity}</TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </TableContainer>
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Tutup</Button>
        <Button
          startIcon={<Print />}
          variant="contained"
          onClick={handlePrint}
          sx={{
            backgroundColor: 'black',
            '&:hover': {
              backgroundColor: '#333',
            },
          }}
        >
          Cetak Label
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export const ShippingPage = () => {
  const [selectedShipment, setSelectedShipment] = useState<Shipment | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [filterAnchorEl, setFilterAnchorEl] = useState<null | HTMLElement>(null);
  const [selectedStatus, setSelectedStatus] = useState('all');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [stats, setStats] = useState<ShipmentStats>({
    totalShipments: 0,
    inTransit: 0,
    delivered: 0,
    processing: 0,
    outForDelivery: 0,
  });

  const [shipments, setShipments] = useState<Shipment[]>([]);

  useEffect(() => {
    fetchStats();
    fetchShipments();
  }, [searchQuery, selectedStatus]);

  const fetchStats = async () => {
    try {
      const response = await api.getShipmentStats();
      setStats(response.data);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch shipping stats');
    }
  };

  const fetchShipments = async () => {
    try {
      setLoading(true);
      const params: Record<string, any> = {};
      if (searchQuery) {
        params.search = searchQuery;
      }
      if (selectedStatus !== 'all') {
        params.status = selectedStatus;
      }
      const response = await api.getShipments(params);
      setShipments(response.data);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch shipments');
    } finally {
      setLoading(false);
    }
  };

  const handleStatusUpdate = async (id: string, status: string) => {
    try {
      await api.updateShipmentStatus(id, status);
      await fetchShipments(); // Refresh the list
      await fetchStats(); // Refresh the stats
    } catch (err: any) {
      setError(err.message || 'Failed to update shipment status');
    }
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
      case 'delivered':
        return 'success';
      case 'in_transit':
        return 'info';
      case 'out_for_delivery':
        return 'warning';
      default:
        return 'error';
    }
  };

  const getStatusLabel = (status: string): string => {
    return status.split('_').map(word => 
      word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
  };

  const filteredShipments = (shipments || []).filter((shipment) => {
    const matchesSearch =
      String(shipment.id).toLowerCase().includes(searchQuery.toLowerCase()) ||
      shipment.customer.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      shipment.courier.trackingNumber.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesStatus =
      selectedStatus === 'all' || shipment.status === selectedStatus;
    return matchesSearch && matchesStatus;
  });

  if (loading) {
    return (
      <Box sx={{ p: 3 }}>
        <Typography>Loading...</Typography>
      </Box>
    );
  }

  if (error) {
    return (
      <Box sx={{ p: 3 }}>
        <Typography color="error">{error}</Typography>
      </Box>
    );
  }

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 4 }}>
        Pengiriman
      </Typography>

      <Grid container spacing={3} sx={{ mb: 4 }}>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <LocalShipping sx={{ fontSize: 40, color: 'primary.main' }} />
                <Typography variant="h4">{stats.totalShipments}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Total Pengiriman
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <Timeline sx={{ fontSize: 40, color: 'info.main' }} />
                <Typography variant="h4">{stats.inTransit}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Dalam Pengiriman
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <LocationOn sx={{ fontSize: 40, color: 'success.main' }} />
                <Typography variant="h4">{stats.delivered}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Terkirim
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <LocalMall sx={{ fontSize: 40, color: 'warning.main' }} />
                <Typography variant="h4">{stats.processing}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Diproses
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
            placeholder="Cari pengiriman..."
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
                <TableCell>ID Pengiriman</TableCell>
                <TableCell>ID Pesanan</TableCell>
                <TableCell>Penerima</TableCell>
                <TableCell>Kurir</TableCell>
                <TableCell>No. Resi</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Tanggal Update</TableCell>
                <TableCell align="right">Aksi</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
                  {filteredShipments.map((shipment, index) => (
                    <TableRow key={`${shipment.id}-${index}`}>
                  <TableCell>{shipment.id}</TableCell>
                  <TableCell>{shipment.orderId}</TableCell>
                  <TableCell>
                    <Stack>
                      <Typography variant="body2">{shipment.customer.name}</Typography>
                      <Typography variant="caption" color="text.secondary">
                        {shipment.customer.phone}
                      </Typography>
                    </Stack>
                  </TableCell>
                  <TableCell>{shipment.courier.name}</TableCell>
                  <TableCell>{shipment.courier.trackingNumber}</TableCell>
                  <TableCell>
                    <Chip
                      size="small"
                      label={getStatusLabel(shipment.status)}
                      color={getStatusColor(shipment.status)}
                    />
                  </TableCell>
                  <TableCell>
                    {new Date(shipment.updatedAt).toLocaleDateString('id-ID')}
                  </TableCell>
                  <TableCell align="right">
                    <IconButton
                      size="small"
                      onClick={() => setSelectedShipment(shipment)}
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
        <MenuItem onClick={() => handleStatusFilter('processing')}>
          Processing
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('in_transit')}>
          In Transit
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('out_for_delivery')}>
          Out for Delivery
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('delivered')}>
          Delivered
        </MenuItem>
      </Menu>

      <ShipmentDetailDialog
        open={Boolean(selectedShipment)}
        shipment={selectedShipment}
        onClose={() => setSelectedShipment(null)}
      />
    </Box>
  );
};
