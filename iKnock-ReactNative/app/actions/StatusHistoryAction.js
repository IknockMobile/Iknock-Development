import {
    GET_STATUS_HISTORY,
    GET_STATUS_HISTORY_FAIL,
    GET_STATUS_HISTORY_SUCCESS,

    STATUS_LOADING,
    STATUS_REFRESHING,
} from './types';
import constant from '../HttpServiceManager/constant';
import httpServiceManager from '../HttpServiceManager/HttpServiceManager';

export const getStatusHistory = (page, is_refreshing = false, lead_id, query = "", cbSuccess, cbFailer) => {

    let params = {
        lead_id,
        page,
        'search': query
    }
    return (dispatch) => {
        if (is_refreshing) {
            dispatch({
                type: STATUS_REFRESHING,
            });
        }

        httpServiceManager.getInstance().request(constant.statusHistory, params, 'get').
            then((response) => {
                if (is_refreshing) {
                    dispatch({
                        type: GET_STATUS_HISTORY_SUCCESS,
                        payload: response
                    });
                } else {
                    dispatch({
                        type: GET_STATUS_HISTORY,
                        payload: response
                    });
                }
                cbSuccess(response.data);//call Back
            }).catch((error) => {
                dispatch({
                    type: GET_STATUS_HISTORY_FAIL,
                    payload: error
                });
                cbFailer(error);//call Back
            });
    }
}
