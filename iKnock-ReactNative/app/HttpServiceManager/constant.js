const constant = {
  baseUrl: 'https://iknockapp.com/api',
  // baseUrl: 'https://staging.iknockapp.com/api',
  serverBaseUrl: 'https://staging.iknockapp.com/api',
  login: 'user/login',
  forgotPassword: 'user/forgot/password',

  getLeadList: 'lead/list',
  getLeadMap:'lead/map/list',
  getUserLead: 'user/lead',

  statusList: 'status/list',
  leadDetail: 'lead/',
  statusHistory: 'lead/history',
  getTenantUserList: 'tenant/user/list',
  getTypeList: 'type/list',
  userAssignLead: 'user/assign/lead',
  leadStatusUpdate: 'lead/status/update',
  leadMedia: 'lead/media',

  addLeadQuery: 'lead/query',
  userLeadAppointmentCreate: 'user/lead/appointment/create',

  getMyappointment: 'user/lead/appointment/list',
  userLeadAppointmentExecute: 'user/lead/appointment/execute',
  userMarketingAppointmentCreate: 'user/marketing/appointment/create',
  marketingTemplateList: 'marketing/template/list',

  userTrainingList: 'user/training/list',

  userLeadReport: 'user/lead/report',
  userLeadStatusReport: 'user/lead/status/report',
  userCommissionReport: 'user/commission/report',
  userOutboundAppointmentCreate: 'user/outbound/appointment/create',
};

export default constant;
