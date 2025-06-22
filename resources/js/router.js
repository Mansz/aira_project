import { createBrowserRouter } from "react-router-dom";
import App from "./App";
// import other routes/components as needed

const router = createBrowserRouter([
  {
    path: "/",
    element: <App />,
    // define child routes here
  },
]);

export default router;

// Untuk mengaktifkan future flag v7_startTransition, tambahkan konfigurasi berikut saat membuat router:
// import { createBrowserRouter, createRoutesFromElements, Route } from "react-router-dom";
// const router = createBrowserRouter(routes, {
//   future: { v7_startTransition: true }
// });
