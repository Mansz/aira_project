import { useState } from 'react';
import {
  Box,
  Card,
  CardContent,
  TextField,
  Button,
  Typography,
  InputAdornment,
  IconButton,
  Alert,
  Stack,
} from '@mui/material';
import {
  Visibility,
  VisibilityOff,
  Login as LoginIcon,
} from '@mui/icons-material';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '@/hooks/useAuth';
import logoImage from '@/assets/images/3.png';

export const LoginPage = () => {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      // Here you would make an API call to authenticate
      await login(formData.email, formData.password);
      navigate('/dashboard');
    } catch (err: any) {
      setError(err.message || 'Login gagal. Silakan coba lagi.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Box
      sx={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        bgcolor: '#f5f5f5',
      }}
    >
      <Box sx={{ width: '100%', maxWidth: 400, p: 2 }}>
        <Card
          elevation={4}
          sx={{
            borderRadius: 2,
            bgcolor: 'background.paper',
          }}
        >
          <CardContent sx={{ p: 4 }}>
            <Stack spacing={3}>
              <Box sx={{ textAlign: 'center' }}>
                <Box
                  component="img"
                  src={logoImage}
                  alt="AIRA Grosir Logo"
                  sx={{
                    height: 60,
                    mb: 2
                  }}
                />
                <Typography
                  variant="h4"
                  component="h1"
                  sx={{
                    fontWeight: 700,
                    color: 'text.primary',
                    mb: 1,
                  }}
                >
                  AIRA Grosir
                </Typography>
                <Typography
                  variant="body2"
                  sx={{ color: 'text.secondary' }}
                >
                  Admin Panel
                </Typography>
              </Box>

              {error && (
                <Alert severity="error" sx={{ mt: 2 }}>
                  {error}
                </Alert>
              )}

              <form onSubmit={handleSubmit}>
                <Stack spacing={3}>
                  <TextField
                    fullWidth
                    label="Email"
                    type="email"
                    value={formData.email}
                    onChange={(e) =>
                      setFormData({ ...formData, email: e.target.value })
                    }
                    required
                  />

                  <TextField
                    fullWidth
                    label="Password"
                    type={showPassword ? 'text' : 'password'}
                    value={formData.password}
                    onChange={(e) =>
                      setFormData({ ...formData, password: e.target.value })
                    }
                    required
                    InputProps={{
                      endAdornment: (
                        <InputAdornment position="end">
                          <IconButton
                            onClick={() => setShowPassword(!showPassword)}
                            edge="end"
                          >
                            {showPassword ? <VisibilityOff /> : <Visibility />}
                          </IconButton>
                        </InputAdornment>
                      ),
                    }}
                  />

                  <Button
                    type="submit"
                    variant="contained"
                    size="large"
                    disabled={loading}
                    startIcon={<LoginIcon />}
                    sx={{
                      backgroundColor: 'black',
                      '&:hover': {
                        backgroundColor: '#333',
                      },
                    }}
                  >
                    {loading ? 'Memproses...' : 'Login'}
                  </Button>
                </Stack>
              </form>

              <Typography
                variant="body2"
                align="center"
                sx={{ color: 'text.secondary', mt: 3 }}
              >
                Lupa password? Hubungi administrator sistem
              </Typography>
            </Stack>
          </CardContent>
        </Card>

        <Typography
          variant="body2"
          align="center"
          sx={{ color: 'text.secondary', mt: 4 }}
        >
          &copy; {new Date().getFullYear()} AIRA Grosir Cirebon.
        </Typography>
      </Box>
    </Box>
  );
};
