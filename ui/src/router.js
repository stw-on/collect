import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

const routes = [
  {
    path: '/upload/:templateId',
    name: 'upload',
    component: require('./pages/upload-page.vue').default,
  },
  {
    path: '/download/:transferId/:accessToken',
    name: 'download',
    component: require('./pages/download-page.vue').default,
  },
]

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes,
})

export default router
