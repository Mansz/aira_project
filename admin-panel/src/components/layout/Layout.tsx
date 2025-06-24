import { useState } from 'react';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import logoImage from '@/assets/images/3.png';
import {
  Box,
  Drawer,
  AppBar,
  Toolbar,
  List,
  Typography,
  Divider,
  IconButton,
  ListItem,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Avatar,
  Menu,
  MenuItem,
  useTheme,
  useMediaQuery,
  Collapse,
} from '@mui/material';
import {
  Menu as MenuIcon,
  Dashboard,
  ShoppingCart,
  People,
  Settings,
  Logout,
  ExpandLess,
  ExpandMore,
  Inventory,
  LocalShipping,
  Payment,
  LiveTv,
  WhatsApp,
  Receipt,
  LocalOffer,
  Message,
  Reply,
  AdminPanelSettings,
} from '@mui/icons-material';
import { useAuthStore } from '@/stores/useAuthStore';

const drawerWidth = 280;

interface MenuItem {
  title: string;
  path?: string;
  icon: JSX.Element;
  children?: {
    title: string;
    path: string;
    icon: JSX.Element;
  }[];
}

const menuItems: MenuItem[] = [
  {
    title: 'Dashboard',
    path: '/dashboard',
    icon: <Dashboard />,
  },
  {
    title: 'Live Streaming',
    icon: <LiveTv />,
    children: [
      
      {
        title: 'Daftar Streaming',
        path: '/streaming/table',
        icon: <LiveTv />,
      },
      {
        title: 'Live Orders',
        path: '/live/orders',
        icon: <Receipt />,
      },
      {
        title: 'Live Vouchers',
        path: '/live/vouchers',
        icon: <LocalOffer />,
      },
    ],
  },
      {
        title: 'E-commerce',
        icon: <ShoppingCart />,
        children: [
          {
            title: 'Products',
            path: '/products',
            icon: <Inventory />,
          },
          {
            title: 'Orders',
            path: '/orders',
            icon: <Receipt />,
          },
          {
            title: 'Order Complaints',
            path: '/orders/complaints',
            icon: <Receipt />,
          },
          {
            title: 'Payments',
            path: '/payments',
            icon: <Payment />,
          },
          {
            title: 'Payment Settings',
            path: '/payment-settings',
            icon: <Settings />,
          },
          {
            title: 'Payment Proofs',
            path: '/payment-proofs',
            icon: <Receipt />,
          },
          {
            title: 'Shipping',
            path: '/shipping',
            icon: <LocalShipping />,
          },
        ],
      },
  {
    title: 'Management',
    icon: <People />,
    children: [
      {
        title: 'Users',
        path: '/users',
        icon: <People />,
      },
      {
        title: 'Admins',
        path: '/admins',
        icon: <AdminPanelSettings />,
      },
    ],
  },
  {
    title: 'WhatsApp',
    icon: <WhatsApp />,
    children: [
      {
        title: 'Messages',
        path: '/whatsapp',
        icon: <Message />,
      },
      {
        title: 'Auto Replies',
        path: '/whatsapp/auto-replies',
        icon: <Reply />,
      },
    ],
  },
  {
    title: 'Settings',
    path: '/settings',
    icon: <Settings />,
  },
];

export const Layout = () => {
  const theme = useTheme();
  const navigate = useNavigate();
  const location = useLocation();
  const { logout } = useAuthStore();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  
  const [mobileOpen, setMobileOpen] = useState(false);
  const [expandedMenu, setExpandedMenu] = useState<string | null>(null);
  const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);

  const handleDrawerToggle = () => {
    setMobileOpen(!mobileOpen);
  };

  const handleMenuClick = (title: string) => {
    setExpandedMenu(expandedMenu === title ? null : title);
  };

  const handleProfileMenuOpen = (event: React.MouseEvent<HTMLElement>) => {
    setAnchorEl(event.currentTarget);
  };

  const handleProfileMenuClose = () => {
    setAnchorEl(null);
  };

  const handleLogout = () => {
    handleProfileMenuClose();
    logout();
    navigate('/login');
  };

  const isPathActive = (path: string) => {
    return location.pathname === path;
  };

  const isMenuActive = (item: MenuItem) => {
    if (item.path) {
      return isPathActive(item.path);
    }
    return item.children?.some((child) => isPathActive(child.path)) || false;
  };

  const drawer = (
    <Box>
      <Toolbar sx={{ px: 2, display: 'flex', alignItems: 'center', gap: 2 }}>
        <Box
          component="img"
          src={logoImage}
          alt="AIRA Grosir Logo"
          sx={{
            height: 40
          }}
        />
        <Typography variant="h6" noWrap component="div" sx={{ fontWeight: 700 }}>
          AIRA Grosir
        </Typography>
      </Toolbar>
      <Divider />
      <List>
        {menuItems.map((item) => (
          <Box key={item.title}>
            <ListItem disablePadding>
              <ListItemButton
                onClick={() => {
                  if (item.children) {
                    handleMenuClick(item.title);
                  } else if (item.path) {
                    navigate(item.path);
                    if (isMobile) {
                      setMobileOpen(false);
                    }
                  }
                }}
                selected={isMenuActive(item)}
                sx={{
                  '&.Mui-selected': {
                    backgroundColor: 'rgba(0, 0, 0, 0.04)',
                  },
                }}
              >
                <ListItemIcon sx={{ color: isMenuActive(item) ? 'black' : 'inherit' }}>
                  {item.icon}
                </ListItemIcon>
                <ListItemText primary={item.title} />
                {item.children && (
                  expandedMenu === item.title ? <ExpandLess /> : <ExpandMore />
                )}
              </ListItemButton>
            </ListItem>
            {item.children && (
              <Collapse in={expandedMenu === item.title} timeout="auto" unmountOnExit>
                <List component="div" disablePadding>
                  {item.children.map((child) => (
                    <ListItemButton
                      key={child.path}
                      sx={{ pl: 4 }}
                      onClick={() => {
                        navigate(child.path);
                        if (isMobile) {
                          setMobileOpen(false);
                        }
                      }}
                      selected={isPathActive(child.path)}
                    >
                      <ListItemIcon sx={{ color: isPathActive(child.path) ? 'black' : 'inherit' }}>
                        {child.icon}
                      </ListItemIcon>
                      <ListItemText primary={child.title} />
                    </ListItemButton>
                  ))}
                </List>
              </Collapse>
            )}
          </Box>
        ))}
      </List>
    </Box>
  );

  return (
    <Box sx={{ display: 'flex', minHeight: '100vh' }}>
      <AppBar
        position="fixed"
        sx={{
          width: { md: `calc(100% - ${drawerWidth}px)` },
          ml: { md: `${drawerWidth}px` },
          bgcolor: 'background.paper',
          color: 'text.primary',
          boxShadow: 1,
        }}
      >
        <Toolbar>
          <IconButton
            color="inherit"
            edge="start"
            onClick={handleDrawerToggle}
            sx={{ mr: 2, display: { md: 'none' } }}
          >
            <MenuIcon />
          </IconButton>

          <Box sx={{ flexGrow: 1 }} />

          <IconButton
            onClick={handleProfileMenuOpen}
            size="small"
            sx={{ ml: 2 }}
          >
            <Avatar
              sx={{
                width: 32,
                height: 32,
                bgcolor: 'black',
              }}
            >
              A
            </Avatar>
          </IconButton>

          <Menu
            anchorEl={anchorEl}
            open={Boolean(anchorEl)}
            onClose={handleProfileMenuClose}
            onClick={handleProfileMenuClose}
            transformOrigin={{ horizontal: 'right', vertical: 'top' }}
            anchorOrigin={{ horizontal: 'right', vertical: 'bottom' }}
          >
            <MenuItem onClick={handleLogout}>
              <ListItemIcon>
                <Logout fontSize="small" />
              </ListItemIcon>
              Logout
            </MenuItem>
          </Menu>
        </Toolbar>
      </AppBar>

      <Box
        component="nav"
        sx={{ width: { md: drawerWidth }, flexShrink: { md: 0 } }}
      >
        <Drawer
          variant="temporary"
          open={mobileOpen}
          onClose={handleDrawerToggle}
          ModalProps={{
            keepMounted: true, // Better open performance on mobile.
          }}
          sx={{
            display: { xs: 'block', md: 'none' },
            '& .MuiDrawer-paper': {
              boxSizing: 'border-box',
              width: drawerWidth,
            },
          }}
        >
          {drawer}
        </Drawer>
        <Drawer
          variant="permanent"
          sx={{
            display: { xs: 'none', md: 'block' },
            '& .MuiDrawer-paper': {
              boxSizing: 'border-box',
              width: drawerWidth,
            },
          }}
          open
        >
          {drawer}
        </Drawer>
      </Box>

      <Box
        component="main"
        sx={{
          flexGrow: 1,
          p: 3,
          width: { md: `calc(100% - ${drawerWidth}px)` },
          minHeight: '100vh',
          bgcolor: '#f5f5f5',
        }}
      >
        <Toolbar />
        <Outlet />
      </Box>
    </Box>
  );
};
