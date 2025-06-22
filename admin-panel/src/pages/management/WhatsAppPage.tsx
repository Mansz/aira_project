import { useState } from 'react';
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
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Switch,
  FormControlLabel,
} from '@mui/material';
import {
  Search,
  Add,
  Edit,
  Delete,
  WhatsApp,
  Message,
  Send,
  Settings,
  Sync,
  QrCode,
} from '@mui/icons-material';

// Mock data for WhatsApp templates
const templates = [
  {
    id: 1,
    name: 'Order Confirmation',
    message: 'Halo {customer_name}, pesanan Anda #{order_id} telah dikonfirmasi. Total pembayaran: Rp {total}',
    type: 'order',
    status: 'active',
    variables: ['customer_name', 'order_id', 'total'],
    lastUsed: '2024-01-20T14:30:00',
  },
  {
    id: 2,
    name: 'Shipping Update',
    message: 'Pesanan Anda #{order_id} sedang dalam pengiriman. No. Resi: {tracking_number}. Cek status: {tracking_url}',
    type: 'shipping',
    status: 'active',
    variables: ['order_id', 'tracking_number', 'tracking_url'],
    lastUsed: '2024-01-20T13:15:00',
  },
];

interface TemplateFormDialogProps {
  open: boolean;
  template?: any;
  onClose: () => void;
  onSubmit: (data: any) => void;
}

const TemplateFormDialog = ({ open, template, onClose, onSubmit }: TemplateFormDialogProps) => {
  const [formData, setFormData] = useState({
    name: template?.name || '',
    message: template?.message || '',
    type: template?.type || 'order',
    status: template?.status || 'active',
    variables: template?.variables || [],
  });

  const handleSubmit = () => {
    onSubmit(formData);
    onClose();
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        {template ? 'Edit Template' : 'Tambah Template'}
      </DialogTitle>
      <DialogContent>
        <Box sx={{ pt: 2, display: 'flex', flexDirection: 'column', gap: 2 }}>
          <TextField
            fullWidth
            label="Nama Template"
            value={formData.name}
            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
          />
          <TextField
            fullWidth
            label="Pesan"
            multiline
            rows={4}
            value={formData.message}
            onChange={(e) => setFormData({ ...formData, message: e.target.value })}
            helperText="Gunakan {variable_name} untuk variabel dinamis"
          />
          <FormControl fullWidth>
            <InputLabel>Tipe</InputLabel>
            <Select
              value={formData.type}
              label="Tipe"
              onChange={(e) => setFormData({ ...formData, type: e.target.value })}
            >
              <MenuItem value="order">Order</MenuItem>
              <MenuItem value="shipping">Shipping</MenuItem>
              <MenuItem value="payment">Payment</MenuItem>
              <MenuItem value="promotion">Promotion</MenuItem>
            </Select>
          </FormControl>
          <FormControl fullWidth>
            <InputLabel>Status</InputLabel>
            <Select
              value={formData.status}
              label="Status"
              onChange={(e) => setFormData({ ...formData, status: e.target.value })}
            >
              <MenuItem value="active">Aktif</MenuItem>
              <MenuItem value="inactive">Nonaktif</MenuItem>
            </Select>
          </FormControl>
          <TextField
            fullWidth
            label="Variabel"
            value={formData.variables.join(', ')}
            onChange={(e) =>
              setFormData({
                ...formData,
                variables: e.target.value.split(',').map((v) => v.trim()),
              })
            }
            helperText="Pisahkan dengan koma"
          />
        </Box>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Batal</Button>
        <Button
          onClick={handleSubmit}
          variant="contained"
          sx={{
            backgroundColor: 'black',
            '&:hover': {
              backgroundColor: '#333',
            },
          }}
        >
          {template ? 'Simpan' : 'Tambah'}
        </Button>
      </DialogActions>
    </Dialog>
  );
};

interface QRCodeDialogProps {
  open: boolean;
  onClose: () => void;
}

const QRCodeDialog = ({ open, onClose }: QRCodeDialogProps) => (
  <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
    <DialogTitle>Scan QR Code WhatsApp</DialogTitle>
    <DialogContent>
      <Box sx={{ p: 4, textAlign: 'center' }}>
        <QrCode sx={{ fontSize: 200, color: 'text.secondary' }} />
        <Typography variant="body2" color="text.secondary" sx={{ mt: 2 }}>
          Buka WhatsApp di ponsel Anda dan scan QR code ini
        </Typography>
      </Box>
    </DialogContent>
    <DialogActions>
      <Button onClick={onClose}>Tutup</Button>
      <Button
        startIcon={<Sync />}
        variant="contained"
        sx={{
          backgroundColor: 'black',
          '&:hover': {
            backgroundColor: '#333',
          },
        }}
      >
        Refresh QR Code
      </Button>
    </DialogActions>
  </Dialog>
);

export const WhatsAppPage = () => {
  const [openTemplateDialog, setOpenTemplateDialog] = useState(false);
  const [openQRDialog, setOpenQRDialog] = useState(false);
  const [selectedTemplate, setSelectedTemplate] = useState<any>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [typeFilter, setTypeFilter] = useState('all');

  // Stats calculation
  const stats = {
    totalTemplates: templates.length,
    activeTemplates: templates.filter(t => t.status === 'active').length,
    messagesSent: 1234,
    deliveryRate: 98.5,
  };

  const handleOpenTemplateDialog = (template?: any) => {
    setSelectedTemplate(template);
    setOpenTemplateDialog(true);
  };

  const handleCloseTemplateDialog = () => {
    setSelectedTemplate(null);
    setOpenTemplateDialog(false);
  };

  const handleSubmit = (data: any) => {
    console.log('Form data:', data);
    // Here you would make an API call to create/update the template
  };

  const handleDelete = (templateId: number) => {
    if (window.confirm('Anda yakin ingin menghapus template ini?')) {
      console.log('Deleting template:', templateId);
      // Here you would make an API call to delete the template
    }
  };

  const getStatusColor = (status: string): "success" | "error" => {
    return status === 'active' ? 'success' : 'error';
  };

  const filteredTemplates = templates.filter((template) => {
    const matchesSearch =
      template.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      template.message.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesType = typeFilter === 'all' || template.type === typeFilter;
    return matchesSearch && matchesType;
  });

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 4 }}>
        WhatsApp
      </Typography>

      <Grid container spacing={3} sx={{ mb: 4 }}>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <Message sx={{ fontSize: 40, color: 'primary.main' }} />
                <Typography variant="h4">{stats.totalTemplates}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Total Template
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <Send sx={{ fontSize: 40, color: 'success.main' }} />
                <Typography variant="h4">{stats.messagesSent}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Pesan Terkirim
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <WhatsApp sx={{ fontSize: 40, color: 'info.main' }} />
                <Typography variant="h4">{stats.activeTemplates}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Template Aktif
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <Settings sx={{ fontSize: 40, color: 'warning.main' }} />
                <Typography variant="h4">{stats.deliveryRate}%</Typography>
                <Typography variant="body2" color="text.secondary">
                  Tingkat Pengiriman
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      <Paper sx={{ p: 3, mb: 3 }}>
        <Stack spacing={2}>
          <Typography variant="h6">Pengaturan WhatsApp</Typography>
          <Grid container spacing={2} alignItems="center">
            <Grid item xs={12} md={6}>
              <FormControlLabel
                control={<Switch defaultChecked />}
                label="Aktifkan Notifikasi WhatsApp"
              />
            </Grid>
            <Grid item xs={12} md={6}>
              <Stack direction="row" spacing={2} justifyContent="flex-end">
                <Button
                  startIcon={<WhatsApp />}
                  onClick={() => setOpenQRDialog(true)}
                  variant="outlined"
                  sx={{
                    borderColor: 'black',
                    color: 'black',
                    '&:hover': {
                      borderColor: '#333',
                      backgroundColor: 'rgba(0, 0, 0, 0.04)',
                    },
                  }}
                >
                  Hubungkan WhatsApp
                </Button>
                <Button
                  startIcon={<Settings />}
                  variant="contained"
                  sx={{
                    backgroundColor: 'black',
                    '&:hover': {
                      backgroundColor: '#333',
                    },
                  }}
                >
                  Pengaturan Lanjutan
                </Button>
              </Stack>
            </Grid>
          </Grid>
        </Stack>
      </Paper>

      <Paper sx={{ p: 3 }}>
        <Box sx={{ mb: 3, display: 'flex', gap: 2 }}>
          <TextField
            fullWidth
            placeholder="Cari template..."
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
          <FormControl sx={{ minWidth: 120 }}>
            <InputLabel>Tipe</InputLabel>
            <Select
              value={typeFilter}
              label="Tipe"
              onChange={(e) => setTypeFilter(e.target.value)}
            >
              <MenuItem value="all">Semua</MenuItem>
              <MenuItem value="order">Order</MenuItem>
              <MenuItem value="shipping">Shipping</MenuItem>
              <MenuItem value="payment">Payment</MenuItem>
              <MenuItem value="promotion">Promotion</MenuItem>
            </Select>
          </FormControl>
          <Button
            variant="contained"
            startIcon={<Add />}
            onClick={() => handleOpenTemplateDialog()}
            sx={{
              backgroundColor: 'black',
              '&:hover': {
                backgroundColor: '#333',
              },
            }}
          >
            Tambah Template
          </Button>
        </Box>

        <TableContainer>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Nama Template</TableCell>
                <TableCell>Pesan</TableCell>
                <TableCell>Tipe</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Terakhir Digunakan</TableCell>
                <TableCell align="right">Aksi</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {filteredTemplates.map((template) => (
                <TableRow key={template.id}>
                  <TableCell>{template.name}</TableCell>
                  <TableCell>
                    <Typography
                      variant="body2"
                      sx={{
                        maxWidth: 300,
                        whiteSpace: 'nowrap',
                        overflow: 'hidden',
                        textOverflow: 'ellipsis',
                      }}
                    >
                      {template.message}
                    </Typography>
                  </TableCell>
                  <TableCell>
                    <Chip
                      size="small"
                      label={template.type}
                      color="primary"
                      sx={{ textTransform: 'capitalize' }}
                    />
                  </TableCell>
                  <TableCell>
                    <Chip
                      size="small"
                      label={template.status === 'active' ? 'Aktif' : 'Nonaktif'}
                      color={getStatusColor(template.status)}
                    />
                  </TableCell>
                  <TableCell>
                    {new Date(template.lastUsed).toLocaleString('id-ID')}
                  </TableCell>
                  <TableCell align="right">
                    <IconButton
                      size="small"
                      onClick={() => handleOpenTemplateDialog(template)}
                      sx={{ color: 'black' }}
                    >
                      <Edit />
                    </IconButton>
                    <IconButton
                      size="small"
                      onClick={() => handleDelete(template.id)}
                      sx={{ color: 'black' }}
                    >
                      <Delete />
                    </IconButton>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </TableContainer>
      </Paper>

      <TemplateFormDialog
        open={openTemplateDialog}
        template={selectedTemplate}
        onClose={handleCloseTemplateDialog}
        onSubmit={handleSubmit}
      />

      <QRCodeDialog
        open={openQRDialog}
        onClose={() => setOpenQRDialog(false)}
      />
    </Box>
  );
};
