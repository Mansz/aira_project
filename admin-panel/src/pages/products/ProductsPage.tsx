import { useState } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { useNavigate } from 'react-router-dom';
import {
  Box,
  Button,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Typography,
  IconButton,
  CircularProgress,
  Stack,
  Chip,
} from '@mui/material';
import {
  Edit as EditIcon,
  Delete as DeleteIcon,
  Add as AddIcon,
} from '@mui/icons-material';
import axios from 'axios';

interface Product {
  id: number;
  name: string;
  description: string;
  price: number;
  stock: number;
  image: string;
}

export const ProductsPage = () => {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);

  const { data: products, isLoading } = useQuery<Product[]>({
    queryKey: ['products'],
    queryFn: async () => {
      const { data } = await axios.get('/api/v1/admin/products');
      return data.data;
    },
  });

  const handleEdit = (product: Product) => {
    navigate(`/products/${product.id}/edit`);
  };

  const handleDelete = async (product: Product) => {
    if (window.confirm('Are you sure you want to delete this product?')) {
      try {
        await axios.delete(`/api/v1/admin/products/${product.id}`);
        // Refetch products after deletion
        await queryClient.refetchQueries({ queryKey: ['products'] });
      } catch (error) {
        console.error('Error deleting product:', error);
      }
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
      <Stack
        direction="row"
        justifyContent="space-between"
        alignItems="center"
        sx={{ mb: 4 }}
      >
        <Typography variant="h4">Products</Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => navigate('/products/create')}
          sx={{
            backgroundColor: 'black',
            '&:hover': {
              backgroundColor: '#333',
            },
          }}
        >
          Add Product
        </Button>
      </Stack>

      <TableContainer component={Paper} elevation={0}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell>Image</TableCell>
              <TableCell>Name</TableCell>
              <TableCell>Description</TableCell>
              <TableCell>Price</TableCell>
              <TableCell>Stock</TableCell>
              <TableCell>Actions</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {products?.map((product) => (
              <TableRow key={product.id}>
                <TableCell>
                  <Box
                    component="img"
                    src={product.image}
                    alt={product.name}
                    sx={{
                      width: 50,
                      height: 50,
                      objectFit: 'cover',
                      borderRadius: 1,
                    }}
                  />
                </TableCell>
                <TableCell>{product.name}</TableCell>
                <TableCell>{product.description}</TableCell>
                <TableCell>${product.price.toLocaleString()}</TableCell>
                <TableCell>
                  <Chip
                    label={product.stock}
                    color={product.stock > 0 ? 'success' : 'error'}
                    size="small"
                  />
                </TableCell>
                <TableCell>
                  <Stack direction="row" spacing={1}>
                    <IconButton
                      size="small"
                      onClick={() => handleEdit(product)}
                      sx={{ color: 'black' }}
                    >
                      <EditIcon />
                    </IconButton>
                    <IconButton
                      size="small"
                      onClick={() => handleDelete(product)}
                      sx={{ color: 'error.main' }}
                    >
                      <DeleteIcon />
                    </IconButton>
                  </Stack>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>
    </Box>
  );
};