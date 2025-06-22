import React, { useState, useEffect } from 'react';
import {
  Box,
  Paper,
  Typography,
  TextField,
  Button,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Grid,
  Card,
  CardMedia,
  IconButton,
  Alert,
  CircularProgress,
  InputAdornment,
} from '@mui/material';
import {
  ArrowBack as ArrowBackIcon,
  CloudUpload as CloudUploadIcon,
  Delete as DeleteIcon,
} from '@mui/icons-material';
import { useNavigate, useParams } from 'react-router-dom';
import { useSnackbar } from 'notistack';
import { api, Product, ProductCategory } from '../../lib/api';

interface ProductFormData {
  name: string;
  description: string;
  category_id: string;
  price: string;
  stock: string;
  weight: string;
  color: string;
  size: string;
  sku: string;
  status: 'active' | 'inactive';
  image?: File | null;
}

export const ProductForm = () => {
  const navigate = useNavigate();
  const { id } = useParams<{ id: string }>();
  const { enqueueSnackbar } = useSnackbar();
  const isEdit = Boolean(id);

  const [loading, setLoading] = useState(false);
  const [categories, setCategories] = useState<ProductCategory[]>([]);
  const [imagePreview, setImagePreview] = useState<string | null>(null);
  const [formData, setFormData] = useState<ProductFormData>({
    name: '',
    description: '',
    category_id: '',
    price: '',
    stock: '',
    weight: '',
    color: '',
    size: '',
    sku: '',
    status: 'active',
    image: null,
  });
  const [errors, setErrors] = useState<Record<string, string[]>>({});

  useEffect(() => {
    fetchCategories();
    if (isEdit && id) {
      fetchProduct(parseInt(id));
    }
  }, [isEdit, id]);

  const fetchCategories = async () => {
    try {
      const response = await api.getCategories();
      setCategories(response.data);
    } catch (err: any) {
      console.error('Error fetching categories:', err);
      enqueueSnackbar('Gagal mengambil data kategori', { variant: 'error' });
    }
  };

  const fetchProduct = async (productId: number) => {
    try {
      setLoading(true);
      const response = await api.getProduct(productId);
      const product = response.data;
      
      setFormData({
        name: product.name,
        description: product.description,
        category_id: product.category_id.toString(),
        price: product.price.toString(),
        stock: product.stock.toString(),
        weight: product.weight?.toString() || '',
        color: product.color || '',
        size: product.size || '',
        sku: product.sku || '',
        status: product.status as 'active' | 'inactive',
        image: null,
      });

      if (product.image) {
        setImagePreview(product.image);
      }
    } catch (err: any) {
      console.error('Error fetching product:', err);
      enqueueSnackbar('Gagal mengambil data produk', { variant: 'error' });
      navigate('/products');
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (field: keyof ProductFormData) => (
    event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    setFormData(prev => ({
      ...prev,
      [field]: event.target.value,
    }));
    
    // Clear error for this field
    if (errors[field]) {
      setErrors(prev => ({
        ...prev,
        [field]: [],
      }));
    }
  };

  const handleSelectChange = (field: keyof ProductFormData) => (
    event: any
  ) => {
    setFormData(prev => ({
      ...prev,
      [field]: event.target.value,
    }));
    
    // Clear error for this field
    if (errors[field]) {
      setErrors(prev => ({
        ...prev,
        [field]: [],
      }));
    }
  };

  const handleImageChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (file) {
      setFormData(prev => ({
        ...prev,
        image: file,
      }));

      // Create preview
      const reader = new FileReader();
      reader.onload = (e) => {
        setImagePreview(e.target?.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleRemoveImage = () => {
    setFormData(prev => ({
      ...prev,
      image: null,
    }));
    setImagePreview(null);
  };

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    setLoading(true);
    setErrors({});

    try {
      const submitData = new FormData();
      
      // Add all form fields
      submitData.append('name', formData.name);
      submitData.append('description', formData.description);
      submitData.append('category_id', formData.category_id);
      submitData.append('price', formData.price);
      submitData.append('stock', formData.stock);
      submitData.append('status', formData.status);
      
      if (formData.weight) submitData.append('weight', formData.weight);
      if (formData.color) submitData.append('color', formData.color);
      if (formData.size) submitData.append('size', formData.size);
      if (formData.sku) submitData.append('sku', formData.sku);
      
      // Add image if selected
      if (formData.image) {
        submitData.append('image', formData.image);
      }

      let response;
      if (isEdit && id) {
        response = await api.updateProduct(parseInt(id), submitData);
      } else {
        response = await api.createProduct(submitData);
      }

      enqueueSnackbar(response.message || `Produk berhasil ${isEdit ? 'diperbarui' : 'dibuat'}`, {
        variant: 'success',
      });

      navigate('/products');
    } catch (err: any) {
      console.error('Error saving product:', err);
      
      if (err.errors) {
        setErrors(err.errors);
      }
      
      enqueueSnackbar(err.message || `Gagal ${isEdit ? 'memperbarui' : 'membuat'} produk`, {
        variant: 'error',
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Box>
      <Box sx={{ mb: 4, display: 'flex', alignItems: 'center', gap: 2 }}>
        <IconButton onClick={() => navigate('/products')}>
          <ArrowBackIcon />
        </IconButton>
        <Typography variant="h4">
          {isEdit ? 'Edit Produk' : 'Tambah Produk'}
        </Typography>
      </Box>

      <Paper sx={{ p: 3 }}>
        <form onSubmit={handleSubmit}>
          <Grid container spacing={3}>
            {/* Basic Information */}
            <Grid item xs={12} md={8}>
              <Typography variant="h6" gutterBottom>
                Informasi Produk
              </Typography>
              
              <Grid container spacing={2}>
                <Grid item xs={12}>
                  <TextField
                    fullWidth
                    label="Nama Produk"
                    value={formData.name}
                    onChange={handleInputChange('name')}
                    error={Boolean(errors.name)}
                    helperText={errors.name?.[0]}
                    required
                  />
                </Grid>
                
                <Grid item xs={12}>
                  <TextField
                    fullWidth
                    label="Deskripsi"
                    value={formData.description}
                    onChange={handleInputChange('description')}
                    error={Boolean(errors.description)}
                    helperText={errors.description?.[0]}
                    multiline
                    rows={4}
                    required
                  />
                </Grid>
                
                <Grid item xs={12} sm={6}>
                  <FormControl fullWidth error={Boolean(errors.category_id)}>
                    <InputLabel>Kategori</InputLabel>
                    <Select
                      value={formData.category_id}
                      onChange={handleSelectChange('category_id')}
                      label="Kategori"
                      required
                    >
                      {categories.map((category) => (
                        <MenuItem key={category.id} value={category.id.toString()}>
                          {category.name}
                        </MenuItem>
                      ))}
                    </Select>
                    {errors.category_id && (
                      <Typography variant="caption" color="error" sx={{ mt: 0.5, ml: 1.5 }}>
                        {errors.category_id[0]}
                      </Typography>
                    )}
                  </FormControl>
                </Grid>
                
                <Grid item xs={12} sm={6}>
                  <FormControl fullWidth>
                    <InputLabel>Status</InputLabel>
                    <Select
                      value={formData.status}
                      onChange={handleSelectChange('status')}
                      label="Status"
                    >
                      <MenuItem value="active">Aktif</MenuItem>
                      <MenuItem value="inactive">Tidak Aktif</MenuItem>
                    </Select>
                  </FormControl>
                </Grid>
                
                <Grid item xs={12} sm={6}>
                  <TextField
                    fullWidth
                    label="Harga"
                    value={formData.price}
                    onChange={handleInputChange('price')}
                    error={Boolean(errors.price)}
                    helperText={errors.price?.[0]}
                    type="number"
                    InputProps={{
                      startAdornment: <InputAdornment position="start">Rp</InputAdornment>,
                    }}
                    required
                  />
                </Grid>
                
                <Grid item xs={12} sm={6}>
                  <TextField
                    fullWidth
                    label="Stok"
                    value={formData.stock}
                    onChange={handleInputChange('stock')}
                    error={Boolean(errors.stock)}
                    helperText={errors.stock?.[0]}
                    type="number"
                    required
                  />
                </Grid>
                
                <Grid item xs={12} sm={6}>
                  <TextField
                    fullWidth
                    label="Berat (gram)"
                    value={formData.weight}
                    onChange={handleInputChange('weight')}
                    error={Boolean(errors.weight)}
                    helperText={errors.weight?.[0]}
                    type="number"
                  />
                </Grid>
                
                <Grid item xs={12} sm={6}>
                  <TextField
                    fullWidth
                    label="SKU"
                    value={formData.sku}
                    onChange={handleInputChange('sku')}
                    error={Boolean(errors.sku)}
                    helperText={errors.sku?.[0]}
                  />
                </Grid>
                
                <Grid item xs={12} sm={6}>
                  <TextField
                    fullWidth
                    label="Warna"
                    value={formData.color}
                    onChange={handleInputChange('color')}
                    error={Boolean(errors.color)}
                    helperText={errors.color?.[0]}
                  />
                </Grid>
                
                <Grid item xs={12} sm={6}>
                  <TextField
                    fullWidth
                    label="Ukuran"
                    value={formData.size}
                    onChange={handleInputChange('size')}
                    error={Boolean(errors.size)}
                    helperText={errors.size?.[0]}
                  />
                </Grid>
              </Grid>
            </Grid>

            {/* Image Upload */}
            <Grid item xs={12} md={4}>
              <Typography variant="h6" gutterBottom>
                Gambar Produk
              </Typography>
              
              <Card sx={{ mb: 2 }}>
                {imagePreview ? (
                  <Box sx={{ position: 'relative' }}>
                    <CardMedia
                      component="img"
                      height="200"
                      image={imagePreview}
                      alt="Product preview"
                    />
                    <IconButton
                      sx={{
                        position: 'absolute',
                        top: 8,
                        right: 8,
                        backgroundColor: 'rgba(0, 0, 0, 0.5)',
                        color: 'white',
                        '&:hover': {
                          backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        },
                      }}
                      onClick={handleRemoveImage}
                    >
                      <DeleteIcon />
                    </IconButton>
                  </Box>
                ) : (
                  <Box
                    sx={{
                      height: 200,
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      backgroundColor: 'grey.100',
                    }}
                  >
                    <CloudUploadIcon sx={{ fontSize: 48, color: 'grey.400' }} />
                  </Box>
                )}
              </Card>
              
              <Button
                variant="outlined"
                component="label"
                fullWidth
                startIcon={<CloudUploadIcon />}
              >
                {imagePreview ? 'Ganti Gambar' : 'Upload Gambar'}
                <input
                  type="file"
                  hidden
                  accept="image/*"
                  onChange={handleImageChange}
                />
              </Button>
              
              {errors.image && (
                <Alert severity="error" sx={{ mt: 1 }}>
                  {errors.image[0]}
                </Alert>
              )}
            </Grid>

            {/* Submit Buttons */}
            <Grid item xs={12}>
              <Box sx={{ display: 'flex', gap: 2, justifyContent: 'flex-end' }}>
                <Button
                  variant="outlined"
                  onClick={() => navigate('/products')}
                  disabled={loading}
                >
                  Batal
                </Button>
                <Button
                  type="submit"
                  variant="contained"
                  disabled={loading}
                  sx={{
                    backgroundColor: 'black',
                    '&:hover': {
                      backgroundColor: '#333',
                    },
                  }}
                >
                  {loading ? (
                    <CircularProgress size={24} color="inherit" />
                  ) : (
                    isEdit ? 'Perbarui Produk' : 'Simpan Produk'
                  )}
                </Button>
              </Box>
            </Grid>
          </Grid>
        </form>
      </Paper>
    </Box>
  );
};
