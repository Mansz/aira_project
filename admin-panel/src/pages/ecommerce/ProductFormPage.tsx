import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Box,
  Paper,
  Typography,
  TextField,
  Button,
  Grid,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  InputAdornment,
  Stack,
  IconButton,
  Card,
  CardMedia,
  Alert,
  CircularProgress,
} from '@mui/material';
import { ArrowBack, CloudUpload, Delete } from '@mui/icons-material';
import { useDropzone } from 'react-dropzone';
import { api } from '../../lib/api';

export const ProductFormPage = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEditMode = Boolean(id);

  const [formData, setFormData] = useState({
    name: '',
    description: '',
    category: '',
    price: '',
    stock: '',
    status: 'active',
  });
  const [image, setImage] = useState<File | null>(null);
  const [imagePreview, setImagePreview] = useState<string>('');
  const [error, setError] = useState<string>('');
  const [success, setSuccess] = useState<string>('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (isEditMode && id) {
      fetchProduct();
    }
  }, [isEditMode, id]);

  const fetchProduct = async () => {
    try {
      setLoading(true);
      const response = await api.getProduct(parseInt(id!));
      const productData = response.data;
      
      setFormData({
        name: productData.name,
        description: productData.description || '',
        category: productData.category,
        price: productData.price.toString(),
        stock: productData.stock.toString(),
        status: productData.status,
      });
      
      // Set image preview with full URL
      if (productData.image) {
        setImagePreview(`${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'}/storage/${productData.image}`);
      }
      
      setError('');
    } catch (err: any) {
      setError(err.message || 'Failed to fetch product');
      console.error('Error fetching product:', err);
    } finally {
      setLoading(false);
    }
  };

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    accept: {
      'image/*': ['.jpeg', '.jpg', '.png']
    },
    maxFiles: 1,
    onDrop: (acceptedFiles) => {
      const file = acceptedFiles[0];
      setImage(file);
      setImagePreview(URL.createObjectURL(file));
    },
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    try {
      // Validate form
      if (!formData.name || !formData.price || !formData.stock) {
        throw new Error('Mohon lengkapi semua field yang wajib diisi');
      }

      const formDataToSend = new FormData();
      formDataToSend.append('name', formData.name);
      formDataToSend.append('description', formData.description);
      formDataToSend.append('category', formData.category);
      formDataToSend.append('price', formData.price);
      formDataToSend.append('stock', formData.stock);
      formDataToSend.append('status', formData.status);
      
      if (image) {
        formDataToSend.append('image', image);
      }

      if (isEditMode && id) {
        await api.updateProduct(parseInt(id), formDataToSend);
        setSuccess('Produk berhasil diperbarui');
      } else {
        await api.createProduct(formDataToSend);
        setSuccess('Produk berhasil ditambahkan');
      }
      
      // Navigate back after success
      setTimeout(() => {
        navigate('/products');
      }, 2000);
    } catch (err: any) {
      console.error('Error saving product:', err);
      setError(err.message || 'Gagal menyimpan produk');
    }
  };

  return (
    <Box>
      <Box sx={{ mb: 4, display: 'flex', alignItems: 'center', gap: 2 }}>
        <IconButton onClick={() => navigate('/products')} sx={{ color: 'black' }}>
          <ArrowBack />
        </IconButton>
        <Typography variant="h4">
          {isEditMode ? 'Edit Produk' : 'Tambah Produk'}
        </Typography>
      </Box>

      {error && (
        <Alert severity="error" sx={{ mb: 3 }}>
          {error}
        </Alert>
      )}

      {success && (
        <Alert severity="success" sx={{ mb: 3 }}>
          {success}
        </Alert>
      )}

      {loading ? (
        <Box display="flex" justifyContent="center" alignItems="center" minHeight="400px">
          <CircularProgress />
        </Box>
      ) : (
        <form onSubmit={handleSubmit}>
          <Grid container spacing={3}>
            <Grid item xs={12} md={8}>
              <Paper sx={{ p: 3, mb: 3 }}>
                <Typography variant="h6" sx={{ mb: 3 }}>
                  Informasi Produk
                </Typography>
                <Stack spacing={3}>
                  <TextField
                    fullWidth
                    label="Nama Produk"
                    required
                    value={formData.name}
                    onChange={(e) =>
                      setFormData({ ...formData, name: e.target.value })
                    }
                  />
                  <TextField
                    fullWidth
                    label="Deskripsi"
                    multiline
                    rows={4}
                    value={formData.description}
                    onChange={(e) =>
                      setFormData({ ...formData, description: e.target.value })
                    }
                  />
                  <FormControl fullWidth>
                    <InputLabel>Kategori</InputLabel>
                    <Select
                      value={formData.category}
                      label="Kategori"
                      onChange={(e) =>
                        setFormData({ ...formData, category: e.target.value })
                      }
                    >
                      <MenuItem value="Clothing">Clothing</MenuItem>
                      <MenuItem value="Accessories">Accessories</MenuItem>
                      <MenuItem value="Shoes">Shoes</MenuItem>
                    </Select>
                  </FormControl>
                </Stack>
              </Paper>

              <Paper sx={{ p: 3 }}>
                <Typography variant="h6" sx={{ mb: 3 }}>
                  Harga & Stok
                </Typography>
                <Stack spacing={3}>
                  <TextField
                    fullWidth
                    label="Harga"
                    required
                    type="number"
                    value={formData.price}
                    onChange={(e) =>
                      setFormData({ ...formData, price: e.target.value })
                    }
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start">Rp</InputAdornment>
                      ),
                    }}
                  />
                  <TextField
                    fullWidth
                    label="Stok"
                    required
                    type="number"
                    value={formData.stock}
                    onChange={(e) =>
                      setFormData({ ...formData, stock: e.target.value })
                    }
                  />
                  <FormControl fullWidth>
                    <InputLabel>Status</InputLabel>
                    <Select
                      value={formData.status}
                      label="Status"
                      onChange={(e) =>
                        setFormData({ ...formData, status: e.target.value })
                      }
                    >
                      <MenuItem value="active">Aktif</MenuItem>
                      <MenuItem value="inactive">Nonaktif</MenuItem>
                      <MenuItem value="draft">Draft</MenuItem>
                    </Select>
                  </FormControl>
                </Stack>
              </Paper>
            </Grid>

            <Grid item xs={12} md={4}>
              <Paper sx={{ p: 3 }}>
                <Typography variant="h6" sx={{ mb: 3 }}>
                  Foto Produk
                </Typography>
                <Box
                  {...getRootProps()}
                  sx={{
                    border: '2px dashed',
                    borderColor: isDragActive ? 'primary.main' : 'grey.300',
                    borderRadius: 1,
                    p: 3,
                    textAlign: 'center',
                    cursor: 'pointer',
                    mb: 2,
                  }}
                >
                  <input {...getInputProps()} />
                  <CloudUpload sx={{ fontSize: 40, color: 'text.secondary', mb: 1 }} />
                  <Typography>
                    {isDragActive
                      ? 'Drop file di sini'
                      : 'Drag & drop file atau klik untuk memilih'}
                  </Typography>
                  <Typography variant="caption" color="text.secondary">
                    Format: JPG, PNG (Max 2MB)
                  </Typography>
                </Box>

                {imagePreview && (
                  <Card sx={{ position: 'relative' }}>
                    <CardMedia
                      component="img"
                      height="200"
                      image={imagePreview}
                      alt="Product preview"
                      sx={{ 
                        objectFit: 'contain',
                        backgroundColor: '#f5f5f5'
                      }}
                      onError={(e) => {
                        const target = e.target as HTMLImageElement;
                        target.src = '/placeholder-image.jpg';
                      }}
                    />
                    <IconButton
                      size="small"
                      sx={{
                        position: 'absolute',
                        top: 8,
                        right: 8,
                        bgcolor: 'background.paper',
                      }}
                      onClick={() => {
                        setImage(null);
                        setImagePreview('');
                      }}
                    >
                      <Delete />
                    </IconButton>
                  </Card>
                )}
              </Paper>
            </Grid>
          </Grid>

          <Box sx={{ mt: 3, display: 'flex', gap: 2, justifyContent: 'flex-end' }}>
            <Button
              variant="outlined"
              onClick={() => navigate('/products')}
              sx={{
                borderColor: 'black',
                color: 'black',
                '&:hover': {
                  borderColor: '#333',
                  backgroundColor: 'rgba(0, 0, 0, 0.04)',
                },
              }}
            >
              Batal
            </Button>
            <Button
              type="submit"
              variant="contained"
              sx={{
                backgroundColor: 'black',
                '&:hover': {
                  backgroundColor: '#333',
                },
              }}
            >
              {isEditMode ? 'Simpan Perubahan' : 'Tambah Produk'}
            </Button>
          </Box>
        </form>
      )}
    </Box>
  );
};
