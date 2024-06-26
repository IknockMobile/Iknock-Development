import {
  GET_LEAD,
  GET_LEAD_SUCCESS,
  GET_LEAD_FAIL,
  GET_MAP_LEAD,
  GET_MAP_LEAD_SUCCESS,
  GET_MAP_LEAD_FAIL,
  GET_MAP_MY_LEAD,
  GET_MAP_MY_LEAD_SUCCESS,
  GET_MAP_MY_LEAD_FAIL,
  IS_LOADING_Lead,
  IS_REFRESHING_Lead,
  GET_MYLEAD,
  GET_MYLEAD_FAIL,
  GET_MYLEAD_SUCCESS,
} from '../../../actions/types';
import constant from '../../../HttpServiceManager/constant';
import httpServiceManager from '../../../HttpServiceManager/HttpServiceManager';

export const onLeadListUpdate = (index, item, lead_type) => {
  return dispatch => {
    dispatch({
      type: 'ON_LEAD_ITEM_UPDATE',
      index,
      lead_type,
      payload: item,
    });
  };
};
export const getLeads = (
  page,
  is_refreshing = false,
  query = '',
  time_slot = '',
  target_user_id = '',
  status_id = '',
  lead_type_id = '',
  cbSuccess,
  cbFailer,
) => {
  let params = {
    page,
    time_slot,
    target_user_id,
    status_id,
    lead_type_id,
    search: query,
    is_web: 0,
  };
  return dispatch => {
    if (is_refreshing) {
      dispatch({
        type: IS_REFRESHING_Lead,
      });
    }
    httpServiceManager
      .getInstance()
      .request(constant.getLeadList + '?is_web=0', params, 'get')
      .then(response => {
        if (is_refreshing) {
          dispatch({
            type: GET_LEAD_SUCCESS,
            payload: response,
          });
        } else {
          dispatch({
            type: GET_LEAD,
            payload: response,
          });
        }
        cbSuccess(response.data); //call Back
      })
      .catch(error => {
        dispatch({
          type: GET_LEAD_FAIL,
          payload: error,
        });
        cbFailer(error); //call Back
      });
  };
};

export const getMyLeads = (
  page,
  is_refreshing = false,
  query = '',
  time_slot = '',
  target_user_id = '',
  status_id = '',
  lead_type_id = '',
  cbSuccess,
  cbFailer,
) => {
  let params = {
    page,
    time_slot,
    target_user_id,
    status_id,
    lead_type_id,
    search: query,
  };
  return dispatch => {
    if (is_refreshing) {
      dispatch({
        type: IS_REFRESHING_Lead,
      });
    }
    httpServiceManager
      .getInstance()
      .request(constant.getUserLead, params, 'get')
      .then(response => {
        if (is_refreshing) {
          dispatch({
            type: GET_MYLEAD_SUCCESS,
            payload: response,
          });
        } else {
          dispatch({
            type: GET_MYLEAD,
            payload: response,
          });
        }
        cbSuccess(response.data); //call Back
      })
      .catch(error => {
        dispatch({
          type: GET_MYLEAD_FAIL,
          payload: error,
        });
        cbFailer(error); //call Back
      });
  };
};

export const clearMapData = () => {
  return {
    type: 'CLEAR_MAP_DATA',
  };
};
export const getLeadsForMap = (
  page,
  latitude,
  longitude,
  is_loading = false,
  query = '',
) => {
  let params = {
    latitude: latitude,
    longitude: longitude,
    radius: '50',
    page: page,
    search: query,
  };
  let url = constant.getLeadMap;
  return dispatch => {
    if (is_loading) {
      dispatch({
        type: IS_LOADING_Lead,
      });
    }
    httpServiceManager
      .getInstance()
      .request(url, '', 'get')
      .then(response => {
        dispatch({
          type: GET_MAP_LEAD_SUCCESS,
          payload: response,
        });
      })
      .catch(error => {
        dispatch({
          type: GET_MAP_LEAD_FAIL,
          payload: error,
        });
      });
  };
};

export const getMyLeadsForMap = (
  page,
  latitude,
  longitude,
  is_loading = false,
  type,
  query = '',
) => {
  let params = {
    latitude: latitude,
    longitude: longitude,
    radius: '50',
    page: page,
    search: query,
  };
  let url = constant.getUserLead;
  return dispatch => {
    if (is_loading) {
      dispatch({
        type: IS_LOADING_Lead,
      });
    }
    httpServiceManager
      .getInstance()
      .request(
        url +
          '?latitude=' +
          latitude +
          '&longitude=' +
          longitude +
          '&radius=' +
          50,
        '',
        'get',
      )
      .then(response => {
        dispatch({
          type: GET_MAP_MY_LEAD_SUCCESS,
          payload: response,
        });
      })
      .catch(error => {
        dispatch({
          type: GET_MAP_MY_LEAD_FAIL,
          payload: error,
        });
      });
  };
};
