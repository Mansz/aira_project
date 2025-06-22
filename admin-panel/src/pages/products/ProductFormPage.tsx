import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import {
  Box,
  Paper,
  Typography,
  TextField,
  Button,
  Stack,
  CircularProgress,
  Alert,
} from '@mui/material';
import { useDropzone } from 'react-dropzone';
import axios from 'axios';

interface Product {
  id?: number;
  name: string;
  description: string;
  price: number;
  stock: number;
  image: string;
}

export const ProductFormPage = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const isEdit = Boolean(id);
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formData, setFormData] = useState<Product>({
    name: '',
    description: '',
    price: 0,
    stock: 0,
    image: '',
  });

  const { data: product, isLoading } = useQuery<Product>({
    queryKey: ['product', id],
    queryFn: async () => {
      if (!id) return null;
      const { data } = await axios.get(`/api/v1/admin/products/${id}`);
      return data.data;
    },
    enabled: isEdit,
  });

  useEffect(() => {
    if (product) {
      setFormData(product);
    }
  }, [product]);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    accept: {
      'image/*': ['.jpeg', '.jpg', '.png', '.webp'],
    },
    multiple: false,
    onDrop: async (acceptedFiles) => {
      const file = acceptedFiles[0];
      if (file) {
        const formData = new FormData();
        formData.append('image', file);
        try {
          const { data } = await axios.post('/api/v1/admin/upload', formData);
          setFormData((prev) => ({ ...prev, image: data.url }));
        } catch (error) {
          console.error('Error uploading image:', error);
        }
      }
    },
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setIsSubmitting(true);

    try {
      if (isEdit) {
        await axios.put(`/api/v1/admin/products/${id}`, formData);
      } else {
        await axios.post('/api/v1/admin/products', formData);
      }
      // Refresh product list after saving
      await queryClient.refetchQueries({ queryKey: ['products'] });
      navigate('/products');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to save product');
    } finally {
      setIsSubmitting(false);
    }
  };

  if (isLoading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', mt: 4 }}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 4 }}>
        {isEdit ? 'Edit Product' : 'Create Product'}
      </Typography>

      <Paper elevation={0} sx={{ p: 3, maxWidth: 800 }}>
        {error && (
          <Alert severity="error" sx={{ mb: 3 }}>
            {error}
          </Alert>
        )}

        <form onSubmit={handleSubmit}>
          <Stack spacing={3}>
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
              {formData.image ? (
                <Box
                  component="img"
                  src={formData.image}
                  alt="Product"
                  sx={{
                    maxWidth: '100%',
                    maxHeight: 200,
                    objectFit: 'contain',
                  }}
                />
              ) : (
                <Typography color="text.secondary">
                  {isDragActive
                    ? 'Drop the image here'
                    : 'Drag and drop an image here, or click to select'}
                </Typography>
              )}
            </Box>

            <TextField
              label="Name"
              required
              value={formData.name}
              onChange={(e) =>
                setFormData((prev) => ({ ...prev, name: e.target.value }))
              }
            />

            <TextField
              label="Description"
              required
              multiline
              rows={4}
              value={formData.description}
              onChange={(e) =>
                setFormData((prev) => ({ ...prev, description: e.target.value }))
              }
            />

            <TextField
              label="Price"
              required
              type="number"
              value={formData.price}
              onChange={(e) =>
                setFormData((prev) => ({
                  ...prev,
                  price: parseFloat(e.target.value),
                }))
              }
            />

            <TextField
              label="Stock"
              required
              type="number"
              value={formData.stock}
              onChange={(e) =>
                setFormData((prev) => ({
                  ...prev,
                  stock: parseInt(e.target.value, 10),
                }))
              }
            />

            <Stack direction="row" spacing={2} justifyContent="flex-end">
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
                Cancel
              </Button>
              <Button
                type="submit"
                variant="contained"
                disabled={isSubmitting}
                sx={{
                  backgroundColor: 'black',
                  '&:hover': {
                    backgroundColor: '#333',
                  },
                }}
              >
                {isSubmitting
                  ? 'Saving...'
                  : isEdit
                  ? 'Update Product'
                  : 'Create Product'}
              </Button>
            </Stack>
          </Stack>
        </form>
      </Paper>
    </Box>
  );
};