import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import ProductsView from '../views/ProductsView.vue'
import ProductDetailView from '../views/ProductDetailView.vue'
import ManageProductsView from '../views/ManageProductsView.vue'
import ProductFormView from '../views/ProductFormView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/login', name: 'login', component: LoginView },
    { path: '/app', name: 'app', component: DashboardView, meta: { requiresAuth: true } },
    { path: '/app/produtos', name: 'manage-products', component: ManageProductsView, meta: { requiresAuth: true } },
    { path: '/app/produtos/novo', name: 'product-new', component: ProductFormView, meta: { requiresAuth: true } },
    { path: '/app/produtos/:id/editar', name: 'product-edit', component: ProductFormView, meta: { requiresAuth: true } },
    { path: '/produtos', name: 'products', component: ProductsView },
    { path: '/produtos/:id', name: 'product-detail', component: ProductDetailView, props: true },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }
  if (to.name === 'login' && auth.isAuthenticated) {
    return { name: 'app' }
  }
  return true
})

export default router
