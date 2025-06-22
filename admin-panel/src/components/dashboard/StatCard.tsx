import {
  Box,
  Card,
  CardContent,
  Typography,
} from '@mui/material';
import {
  TrendingUp,
  TrendingDown,
} from '@mui/icons-material';

interface StatCardProps {
  title: string;
  value: React.ReactNode;
  icon: React.ReactNode;
  trend: number;
  color: string;
}

export const StatCard = ({ title, value, icon, trend, color }: StatCardProps) => (
  <Card>
    <CardContent>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 2 }}>
        <Box sx={{ color }}>{icon}</Box>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
          {trend > 0 ? (
            <TrendingUp sx={{ color: 'success.main' }} />
          ) : (
            <TrendingDown sx={{ color: 'error.main' }} />
          )}
          <Typography
            variant="body2"
            color={trend > 0 ? 'success.main' : 'error.main'}
          >
            {Math.abs(trend)}%
          </Typography>
        </Box>
      </Box>
      <Typography variant="h4" sx={{ mb: 1 }}>
        {value}
      </Typography>
      <Typography variant="body2" color="text.secondary">
        {title}
      </Typography>
    </CardContent>
  </Card>
);
