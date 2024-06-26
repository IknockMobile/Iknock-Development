import {
  GET_REPORT_DATA_SUCCESS,
  GET_REPORT_DATA_FAIL,
  REPORT_IS_LOADING,
  GET_COMMISSION_REPORT_DATA_SUCCESS,
  GET_COMMISSION_REPORT_DATA_FAIL,
  COMMISSION_REPORT_IS_LOADING,
} from './types';
import HttpServiceManager from '../HttpServiceManager/HttpServiceManager';
import constant from '../HttpServiceManager/constant';
//updated
export const getReportsData = (
  page,
  is_loading = false,
  target_user_id = '',
  time_slot = '',
  status_id = '',
  lead_type_id = '',
  cbSuccess,
  cbFailer,
) => {
  // let url = constant.userLeadStatusReport + "?time_slot=" + time_slot + "&target_user_id=" +
  // target_user_id + "&status_id=" + status_id + "&lead_type_id=" + lead_type_id + "&page=" + page;
  let params = {
    time_slot,
    target_user_id,
    status_id,
    lead_type_id,
    page,
  };
  return dispatch => {
    if (is_loading) {
      dispatch({
        type: REPORT_IS_LOADING,
      });
    }
    HttpServiceManager.getInstance()
      .request(constant.userLeadStatusReport, params, 'get')
      .then(response => {
        dispatch({
          type: GET_REPORT_DATA_SUCCESS,
          payload: response,
        });
        cbSuccess(response.data); //call Back
      })
      .catch(error => {
        dispatch({
          type: GET_REPORT_DATA_FAIL,
          payload: error,
        });
        cbFailer(error); //call Back
      });
  };
};

//previous
export const getReportData = (
  is_loading = false,
  target_user_id = '',
  time_slot = '',
  status_id = '',
  lead_type_id = '',
) => {
  let url =
    constant.userLeadReport +
    '?time_slot=' +
    time_slot +
    '&target_user_id=' +
    target_user_id +
    '&status_id=' +
    status_id +
    '&lead_type_id=' +
    lead_type_id;
  return dispatch => {
    if (is_loading) {
      dispatch({
        type: REPORT_IS_LOADING,
      });
    }
    HttpServiceManager.getInstance()
      .request(url, '', 'get')
      .then(response => {
        dispatch({
          type: GET_REPORT_DATA_SUCCESS,
          payload: response,
        });
      })
      .catch(error => {
        dispatch({
          type: GET_REPORT_DATA_FAIL,
          payload: error,
        });
      });
  };
};

export const getCommissionReportData = (
  page,
  is_loading = false,
  target_user_id = '',
  time_slot = '',
  type = '',
  lead_type_id = '',
  cbSuccess,
  cbFailer,
) => {
  let params = {
    time_slot,
    target_user_id,
    type,
    lead_type_id,
  };

  return dispatch => {
    if (is_loading) {
      dispatch({
        type: COMMISSION_REPORT_IS_LOADING,
      });
    }
    HttpServiceManager.getInstance()
      .request(constant.userCommissionReport, params, 'get')
      .then(response => {
        dispatch({
          type: GET_COMMISSION_REPORT_DATA_SUCCESS,
          payload: response,
        });
        cbSuccess(response.data); //call Back
      })
      .catch(error => {
        dispatch({
          type: GET_COMMISSION_REPORT_DATA_FAIL,
          payload: error,
        });
        cbFailer(error); //call Back
      });
  };
};
