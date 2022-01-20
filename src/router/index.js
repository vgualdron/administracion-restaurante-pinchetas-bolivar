import Vue from 'vue'
import Router from 'vue-router'

// Containers
import Full from '@/containers/Full'

// Views
import Home from '@/views/Home'
import Error from '@/views/Error'

import Gasto from '@/views/Gasto'
import TipoProducto from '@/views/TipoProducto'
import Estado from '@/views/Estado'
import Mesa from '@/views/Mesa'
import Producto from '@/views/Producto'
import DependenciaProducto from '@/views/DependenciaProducto'
import CargarStock from '@/views/CargarStock'
import ListarStock from '@/views/ListarStock'
import CierreCaja from '@/views/CierreCaja'
/* import Factura from '@/views/Factura' */
import VerReportes from '@/views/VerReportes'
import ControlFiscal from '@/views/ControlFiscal'
import CopiaFactura from '@/views/CopiaFactura'

Vue.use(Router)
export default new Router({
  mode: 'hash',
  linkActiveClass: 'open active',
  scrollBehavior: () => ({ y: 0 }),
  routes: [
    {
      path: '/',
      redirect: '/home',
      name: 'Home',
      component: Full,
      children: [
        {
          path: 'home',
          name: 'Home',
          component: Home
        },
        {
          path: 'gasto',
          name: 'Gastos',
          component: Gasto
        },
        {
          path: 'tipoproducto',
          name: 'Tipo Producto',
          component: TipoProducto
        },
        {
          path: 'estado',
          name: 'Estado',
          component: Estado
        },
        {
          path: 'mesa',
          name: 'Mesas',
          component: Mesa
        },
        {
          path: 'producto',
          name: 'Productos',
          component: Producto
        },
        {
          path: 'dependenciaproducto',
          name: 'Dependencia  de Productos',
          component: DependenciaProducto
        },
        {
          path: 'cargarstock',
          name: 'Cargar Stock de Productos',
          component: CargarStock
        },
        {
          path: 'listarstock',
          name: 'Listar Stock de Productos',
          component: ListarStock
        },
        {
          path: 'cierrecaja',
          name: 'Cierre Caja',
          component: CierreCaja
        },
        {
          path: 'verreportes',
          name: 'Ver Reportes',
          component: VerReportes
        },
        {
          path: 'controlfiscal',
          name: 'Control Fiscal',
          component: ControlFiscal
        },
        {
          path: 'copiafactura',
          name: 'Copia de Facturas',
          component: CopiaFactura
        },
      ]
    },
    {
      path: '/error',
      name: 'Error',
      component: Error
    }
  ]
})
