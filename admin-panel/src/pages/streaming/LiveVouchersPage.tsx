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

import { useQuery } from '@tanstack/react-query';
import { api } from '../../lib/api';

interface VoucherFormDialogProps {
  open: boolean;
  voucher?: any;
  liveStreams?: any[];
  onClose: () => void;
  onSubmit: (data: any) => void;
}

const VoucherFormDialog = ({ open, voucher, liveStreams, onClose, onSubmit }: VoucherFormDialogProps) => {
  const [formData, setFormData] = useState({
    code: voucher?.code || '',
    discount_value: voucher?.discount_value || '',
    discount_type: voucher?.discount_type || 'percentage',
    live_stream_id: voucher?.live_stream_id || '',
    description: voucher?.description || '',
    start_time: voucher?.start_time ? new Date(voucher.start_time) : new Date(),
    end_time: voucher?.end_time ? new Date(voucher.end_time) : new Date(),
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
          <TextField
            select
            fullWidth
            label="Live Stream"
            value={formData.live_stream_id}
            onChange={(e) =>
              setFormData({ ...formData, live_stream_id: e.target.value })
            }
          >
            {liveStreams?.map((stream: any) => (
              <MenuItem key={stream.id} value={stream.id}>
                {stream.title}
              </MenuItem>
            ))}
          </TextField>
          <Stack direction="row" spacing={2}>
            <TextField
              fullWidth
              label="Nilai Diskon"
              type="number"
              value={formData.discount_value}
              onChange={(e) =>
                setFormData({ ...formData, discount_value: e.target.value })
              }
              InputProps={{
                startAdornment: (
                  <InputAdornment position="start">
                    {formData.discount_type === 'percentage' ? '%' : 'Rp'}
                  </InputAdornment>
                ),
              }}
            />
            <TextField
              select
              fullWidth
              label="Tipe Diskon"
              value={formData.discount_type}
              onChange={(e) =>
                setFormData({ ...formData, discount_type: e.target.value })
              }
            >
              <MenuItem value="percentage">Persentase</MenuItem>
              <MenuItem value="amount">Nominal Tetap</MenuItem>
            </TextField>
          </Stack>
          <TextField
            fullWidth
            label="Deskripsi"
            value={formData.description}
            onChange={(e) =>
              setFormData({ ...formData, description: e.target.value })
            }
            multiline
            rows={2}
          />
          <LocalizationProvider dateAdapter={AdapterDateFns}>
            <Stack direction="row" spacing={2}>
              <DateTimePicker
                label="Berlaku Dari"
                value={formData.start_time}
                onChange={(date) =>
                  setFormData({ ...formData, start_time: date || new Date() })
                }
                sx={{ flex: 1 }}
              />
              <DateTimePicker
                label="Berlaku Sampai"
                value={formData.end_time}
                onChange={(date) =>
                  setFormData({ ...formData, end_time: date || new Date() })
                }
                sx={{ flex: 1 }}
              />
            </Stack>
          </LocalizationProvider>
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

  // Fetch vouchers from API
  const { data: vouchersData, isLoading } = useQuery({
    queryKey: ['liveVouchers'],
    queryFn: () => api.getLiveVouchers(),
  });

  // Fetch live streams for the form dropdown
  const { data: liveStreams } = useQuery({
    queryKey: ['liveStreams'],
    queryFn: () => api.getLiveStreamHistory(),
  });

  const vouchers = vouchersData?.data || [];

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
                <TableCell>Live Stream</TableCell>
                <TableCell>Diskon</TableCell>
                <TableCell>Periode</TableCell>
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
                  <TableCell>{voucher.live_stream?.title || '-'}</TableCell>
                  <TableCell>
                    {voucher.discount_type === 'percentage'
                      ? `${voucher.discount_value}%`
                      : `Rp ${voucher.discount_value.toLocaleString()}`}
                  </TableCell>
                  <TableCell>
                    <Stack spacing={0.5}>
                      <Typography variant="body2">
                        Mulai: {formatDate(voucher.start_time)}
                      </Typography>
                      <Typography variant="body2">
                        Selesai: {formatDate(voucher.end_time)}
                      </Typography>
                    </Stack>
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
        liveStreams={liveStreams?.data || []}
        onClose={handleCloseDialog}
        onSubmit={handleSubmit}
      />
    </Box>
  );
};
