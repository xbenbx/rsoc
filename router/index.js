import Vue from 'vue'
import VueRouter from 'vue-router'
import Login from '@/views/Login.vue'
import Layout from '@/views/Layout.vue'
import Dashboard from '@/views/Dashboard.vue'
import NotFound from '@/views/404.vue'

Vue.use(VueRouter)

/* RSOC管理相关路由 */
const rsocRoutes = [
  {
    path: 'rsoc',
    component: () => import('@/views/rsoc/Index.vue'),
    name: 'RsocList',
    meta: { title: 'RSOC管理', icon: 'el-icon-s-marketing' }
  },
  {
    path: 'rsoc/create',
    component: () => import('@/views/rsoc/Create.vue'),
    name: 'CreateRsoc',
    meta: { title: '创建RSOC', icon: 'el-icon-plus' },
    hidden: true
  },
  {
    path: 'rsoc/edit/:id',
    component: () => import('@/views/rsoc/Edit.vue'),
    name: 'EditRsoc',
    meta: { title: '编辑RSOC', icon: 'el-icon-edit' },
    hidden: true
  },
  {
    path: 'rsoc/detail/:id',
    component: () => import('@/views/rsoc/Detail.vue'),
    name: 'RsocDetail',
    meta: { title: 'RSOC详情', icon: 'el-icon-info' },
    hidden: true
  }
]

/* 关键词管理相关路由 */
const keywordRoutes = [
  {
    path: 'keywords',
    component: () => import('@/views/keywords/Index.vue'),
    name: 'KeywordList',
    meta: { title: '关键词管理', icon: 'el-icon-s-order' }
  },
  {
    path: 'keywords/create',
    component: () => import('@/views/keywords/Create.vue'),
    name: 'CreateKeyword',
    meta: { title: '创建关键词', icon: 'el-icon-plus' },
    hidden: true
  },
  {
    path: 'keywords/edit/:id',
    component: () => import('@/views/keywords/Edit.vue'),
    name: 'EditKeyword',
    meta: { title: '编辑关键词', icon: 'el-icon-edit' },
    hidden: true
  }
]

/* 报表相关路由 */
const reportRoutes = [
  {
    path: 'reports',
    component: () => import('@/views/reports/Index.vue'),
    name: 'Reports',
    meta: { title: '报表分析', icon: 'el-icon-s-data' }
  },
  {
    path: 'reports/performance',
    component: () => import('@/views/reports/Performance.vue'),
    name: 'PerformanceReport',
    meta: { title: '性能报表', icon: 'el-icon-data-line' }
  },
  {
    path: 'reports/keywords',
    component: () => import('@/views/reports/Keywords.vue'),
    name: 'KeywordReport',
    meta: { title: '关键词报表', icon: 'el-icon-document' }
  },
  {
    path: 'reports/revenue',
    component: () => import('@/views/reports/Revenue.vue'),
    name: 'RevenueReport',
    meta: { title: '收入报表', icon: 'el-icon-money' }
  }
]

/* 用户管理相关路由 */
const userRoutes = [
  {
    path: 'users',
    component: () => import('@/views/users/Index.vue'),
    name: 'UserList',
    meta: { title: '用户管理', icon: 'el-icon-user', roles: ['admin'] }
  },
  {
    path: 'users/create',
    component: () => import('@/views/users/Create.vue'),
    name: 'CreateUser',
    meta: { title: '创建用户', icon: 'el-icon-plus', roles: ['admin'] },
    hidden: true
  },
  {
    path: 'users/edit/:id',
    component: () => import('@/views/users/Edit.vue'),
    name: 'EditUser',
    meta: { title: '编辑用户', icon: 'el-icon-edit', roles: ['admin'] },
    hidden: true
  }
]

/* 设置相关路由 */
const settingRoutes = [
  {
    path: 'settings',
    component: () => import('@/views/settings/Index.vue'),
    name: 'Settings',
    meta: { title: '系统设置', icon: 'el-icon-setting' }
  },
  {
    path: 'profile',
    component: () => import('@/views/settings/Profile.vue'),
    name: 'Profile',
    meta: { title: '个人信息', icon: 'el-icon-user' }
  }
]

const routes = [
  {
    path: '/login',
    component: Login,
    meta: { title: '登录' },
    hidden: true
  },
  {
    path: '/',
    component: Layout,
    redirect: '/dashboard',
    meta: { requiresAuth: true },
    children: [
      {
        path: 'dashboard',
        component: Dashboard,
        name: 'Dashboard',
        meta: { title: '仪表盘', icon: 'el-icon-s-home' }
      },
      ...rsocRoutes,
      ...keywordRoutes,
      ...reportRoutes,
      ...userRoutes,
      ...settingRoutes
    ]
  },
  { 
    path: '/404', 
    component: NotFound, 
    hidden: true 
  },
  { 
    path: '*', 
    redirect: '/404', 
    hidden: true 
  }
]

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
})

export default router
