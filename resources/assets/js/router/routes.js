export default ({ authGuard, guestGuard, adminGuard, packingGuard }) => [
  // { path: '/', name: 'welcome', component: require('~/pages/welcome.vue') },
  { path: '/', name: 'welcome', redirect: { name: 'login' } },
  // Authenticated routes.
  ...authGuard([
    { path: '/home', name: 'home', component: require('~/pages/home.vue') },
    { path: '/resume', name: 'resume', component: require('~/pages/admin/resume.vue') },
    { path: '/pdf', name: 'pdf', component: require('~/pages/admin/pdf.vue') },
    { path: '/laporan', name: 'laporan', component: require('~/pages/laporan/laporan.vue') },
    { path: '/memo', name: 'memo', component: require('~/pages/laporan/memo.vue') },
    { path: '/counter', name: 'counter', component: require('~/pages/laporan/counter.vue') },
    { path: '/transfer', name: 'transfer', component: require('~/pages/laporan/transfer.vue') },
    { path: '/bongkaran', name: 'bongkaran', component: require('~/pages/laporan/bongkaran.vue') },
    { path: '/transfer_bongkaran', name: 'transfer_bongkaran', component: require('~/pages/laporan/transfer_bongkaran.vue') },

    { path: '/homektp', name: 'home.ktp', component: require('~/pages/ktp/home.vue') },
    { path: '/applet', name: 'applet', component: require('~/pages/ktp/applet.vue') },
    { path: '/preperso', name: 'preperso', component: require('~/pages/ktp/preperso.vue') },
    { path: '/record', name: 'record', component: require('~/pages/ktp/record.vue') },
    { path: '/periode', name: 'periode', component: require('~/pages/ktp/periode.vue') },
    { path: '/kartu_sam', name: 'kartu_sam', component: require('~/pages/ktp/kartu_sam.vue') },
    { path: '/highchart', name: 'highchart', component: require('~/pages/ktp/highchart.vue') },

    { path: '/input_bongkaran', name: 'input_bongkaran', component: require('~/pages/laporan/input_bongkaran.vue') },

    {
      path: '/settings',
      component: require('~/pages/settings/index.vue'),
      children: [
        { path: '', redirect: { name: 'settings.profile' } },
        { path: 'profile', name: 'settings.profile', component: require('~/pages/settings/profile.vue') },
        { path: 'password', name: 'settings.password', component: require('~/pages/settings/password.vue') }
      ]
    }
  ]),

  ...packingGuard([
    { path: '/packing', name: 'packing', component: require('~/pages/laporan/packing.vue') },

  ]),

  
  ...adminGuard([
    { path: '/monitoring', name: 'monitoring', component: require('~/pages/admin/monitoring.vue') },
    { path: '/cetaklaporan', name: 'cetaklaporan', component: require('~/pages/admin/cetaklaporan.vue') },
    { path: '/proses', name: 'proses', component: require('~/pages/admin/proses.vue') },
    { path: '/pol', name: 'pol', component: require('~/pages/admin/pol.vue') },
    { path: '/pol/:id_pol', name: 'id_pol', component: require('~/pages/admin/data_pol.vue') },
    { path: '/counter/:id_pol', name: 'id_counter', component: require('~/pages/admin/data_counter.vue') },
    { path: '/user', name: 'user', component: require('~/pages/admin/user.vue') },
    
  ]),

  // Guest routes.
  ...guestGuard([
    { path: '/login', name: 'login', component: require('~/pages/auth/login.vue') },
    { path: '/register', name: 'register', component: require('~/pages/auth/register.vue') },
    // { path: '/register', name:'register', redirect: { name: 'login' } },
    { path: '/password/reset', name: 'password.request', component: require('~/pages/auth/password/email.vue') },
    { path: '/password/reset/:token', name: 'password.reset', component: require('~/pages/auth/password/reset.vue') }
  ]),

  { path: '*', component: require('~/pages/errors/404.vue') },
  { path: '/notadmin', name: 'notadmin', component: require('~/pages/errors/notadmin.vue') }
]
