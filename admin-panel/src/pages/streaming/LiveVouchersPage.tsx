import { useState } from 'react';
import {
  Box,
  Paper,
  Typography,
  Button,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Chip,
  IconButton,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  Stack,
  MenuItem,
  InputAdornment,
} from '@mui/material';
import {
  Add,
  Edit,
  Delete,
  LocalOffer,
  Search,
} from '@mui/icons-material';
import { DateTimePicker } from '@mui/x-date-pickers/DateTimePicker';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDateFns } from '@mui/x-date-pickers/AdapterDateFns';

// Mock data for vouchers
const vouchers = [
  {
    id: 1,
    code: 'LIVE10',
    discount: 10,
    type: 'percentage',
    validFrom: '2024-01-20T10:00:00',
    validUntil: '2024-01-20T22:00:00',
    maxUses: 100,
    usedCount: 45,
    status: 'active',
  },
  {
    id: 2,
    code: 'FASHION50K',
    discount: 50000,
    type: 'fixed',
    validFrom: '2024-01-21T14:00:00',
    validUntil: '2024-01-21T20:00:00',
    maxUses: 50,
    usedCount: 0,
    status: 'scheduled',
  },
];

interface VoucherFormDialogProps {
  open: boolean;
  voucher?: any;
  onClose: () => void;
  onSubmit: (data: any) => void;
}

const VoucherFormDialog = ({ open, voucher, onClose, onSubmit }: VoucherFormDialogProps) => {
  const [formData, setFormData] = useState({
    code: voucher?.code || '',
    discount: voucher?.discount || '',
    type: voucher?.type || 'percentage',
    validFrom: voucher?.validFrom ? new Date(voucher.validFrom) : new Date(),
    validUntil: voucher?.validUntil ? new Date(voucher.validUntil) : new Date(),
    maxUses: voucher?.maxUses || '',
    status: voucher?.status || 'active',
  });

  const handleSubmit = () => {
    onSubmit(formData);
    onClose();
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>
        {voucher ? 'Edit Voucher' : 'Tambah Voucher Baru'}
      </DialogTitle>
      <DialogContent>
        <Box sx={{ pt: 2, display: 'flex', flexDirection: 'column', gap: 2 }}>
          <TextField
            fullWidth
            label="Kode Voucher"
            value={formData.code}
            onChange={(e) =>
              setFormData({ ...formData, code: e.target.value.toUpperCase() })
            }
          />
          <Stack direction="row" spacing={2}>
            <TextField
              fullWidth
              label="Nilai Diskon"
              type="number"
              value={formData.discount}
              onChange={(e) =>
                setFormData({ ...formData, discount: e.target.value })
              }
              InputProps={{
                startAdornment: (
                  <InputAdornment position="start">
                    {formData.type === 'percentage' ? '%' : 'Rp'}
                  </InputAdornment>
                ),
              }}
            />
            <TextField
              select
              fullWidth
              label="Tipe Diskon"
              value={formData.type}
              onChange={(e) =>
                setFormData({ ...formData, type: e.target.value })
              }
            >
              <MenuItem value="percentage">Persentase</MenuItem>
              <MenuItem value="fixed">Nominal Tetap</MenuItem>
            </TextField>
          </Stack>
          <LocalizationProvider dateAdapter={AdapterDateFns}>
            <Stack direction="row" spacing={2}>
              <DateTimePicker
                label="Berlaku Dari"
                value={formData.validFrom}
                onChange={(date) =>
                  setFormData({ ...formData, validFrom: date || new Date() })
                }
                sx={{ flex: 1 }}
              />
              <DateTimePicker
                label="Berlaku Sampai"
                value={formData.validUntil}
                onChange={(date) =>
                  setFormData({ ...formData, validUntil: date || new Date() })
                }
                sx={{ flex: 1 }}
              />
            </Stack>
          </LocalizationProvider>
          <TextField
            fullWidth
            label="Maksimal Penggunaan"
            type="number"
            value={formData.maxUses}
            onChange={(e) =>
              setFormData({ ...formData, maxUses: e.target.value })
            }
          />
          <TextField
            select
            fullWidth
            label="Status"
            value={formData.status}
            onChange={(e) =>
              setFormData({ ...formData, status: e.target.value })
            }
          >
            <MenuItem value="active">Aktif</MenuItem>
            <MenuItem value="scheduled">Terjadwal</MenuItem>
            <MenuItem value="inactive">Nonaktif</MenuItem>
          </TextField>
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
          {voucher ? 'Simpan' : 'Tambah'}
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export const LiveVouchersPage = () => {
  const [selectedVoucher, setSelectedVoucher] = useState<any>(null);
  const [openDialog, setOpenDialog] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');

  const handleOpenDialog = (voucher?: any) => {
    setSelectedVoucher(voucher);
    setOpenDialog(true);
  };

  const handleCloseDialog = () => {
    setSelectedVoucher(null);
    setOpenDialog(false);
  };

  const handleSubmit = (data: any) => {
    console.log('Form data:', data);
    // Here you would make an API call to create/update the voucher
  };

  const handleDelete = (voucherId: number) => {
    if (window.confirm('Anda yakin ingin menghapus voucher ini?')) {
      console.log('Deleting voucher:', voucherId);
      // Here you would make an API call to delete the voucher
    }
  };

  const getStatusColor = (status: string): "success" | "warning" | "error" => {
    switch (status) {
      case 'active':
        return 'success';
      case 'scheduled':
        return 'warning';
      default:
        return 'error';
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('id-ID', {
      dateStyle: 'short',
      timeStyle: 'short',
    });
  };

  return (
    <Box>
      <Box sx={{ mb: 4, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Typography variant="h4">Voucher Live</Typography>
        <Button
          variant="contained"
          startIcon={<Add />}
          onClick={() => handleOpenDialog()}
          sx={{
            backgroundColor: 'black',
            '&:hover': {
              backgroundColor: '#333',
            },
          }}
        >
          Tambah Voucher
        </Button>
      </Box>

      <Paper sx={{ p: 3 }}>
        <Box sx={{ mb: 3 }}>
          <TextField
            fullWidth
            placeholder="Cari voucher..."
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
        </Box>

        <TableContainer>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Kode</TableCell>
                <TableCell>Diskon</TableCell>
                <TableCell>Periode</TableCell>
                <TableCell>Penggunaan</TableCell>
                <TableCell>Status</TableCell>
                <TableCell align="right">Aksi</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {vouchers.map((voucher) => (
                <TableRow key={voucher.id}>
                  <TableCell>
                    <Stack direction="row" alignItems="center" spacing={1}>
                      <LocalOffer sx={{ color: 'primary.main', fontSize: 20 }} />
                      {voucher.code}
                    </Stack>
                  </TableCell>
                  <TableCell>
                    {voucher.type === 'percentage'
                      ? `${voucher.discount}%`
                      : `Rp ${voucher.discount.toLocaleString()}`}
                  </TableCell>
                  <TableCell>
                    <Stack spacing={0.5}>
                      <Typography variant="body2">
                        Mulai: {formatDate(voucher.validFrom)}
                      </Typography>
                      <Typography variant="body2">
                        Selesai: {formatDate(voucher.validUntil)}
                      </Typography>
                    </Stack>
                  </TableCell>
                  <TableCell>
                    {voucher.usedCount} / {voucher.maxUses}
                  </TableCell>
                  <TableCell>
                    <Chip
                      size="small"
                      label={voucher.status}
                      color={getStatusColor(voucher.status)}
                    />
                  </TableCell>
                  <TableCell align="right">
                    <IconButton
                      size="small"
                      onClick={() => handleOpenDialog(voucher)}
                      sx={{ color: 'black' }}
                    >
                      <Edit />
                    </IconButton>
                    <IconButton
                      size="small"
                      onClick={() => handleDelete(voucher.id)}
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

      <VoucherFormDialog
        open={openDialog}
        voucher={selectedVoucher}
        onClose={handleCloseDialog}
        onSubmit={handleSubmit}
      />
    </Box>
  );
};
