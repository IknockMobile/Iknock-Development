import {
    LOGIN_USER_SUCCESS,
    LOGIN_USER_FAIL,
    IS__LOGIN_LOADING,

    RESET_PASSWORD_SUCCESS,
    RESET_PASSWORD_FAIL

} from '../../actions/types';
import constant from '../../HttpServiceManager/constant';
import HttpServiceManager from '../../HttpServiceManager/HttpServiceManager';
import { setUser, setUserToken } from '../../UserPreference';

export const loginUser = ({ email, password }, loading = false, cbSuccess, cbFailure) => {
    return (dispatch) => {
        if (loading) {
            dispatch({
                type: IS__LOGIN_LOADING
            });
        }
        HttpServiceManager.getInstance().request(constant.login, { email, password }, 'post').
            then((response) => {
                setUser(response.data);
                setUserToken(response.data[0].token);
                dispatch({
                    type: LOGIN_USER_SUCCESS,
                    payload: response
                });
                cbSuccess(response);
            }).catch((error) => {
                console.log('ERR',error);
                dispatch({
                    type: LOGIN_USER_FAIL,
                    payload: error
                });
                cbFailure(error);
            });
    }
}

export const forgotPassword = ({ email }, loading = false, cbSuccess, cbFailure) => {

    return (dispatch) => {
        if (loading) {
            dispatch({
                type: IS__LOGIN_LOADING
            });
        }
        HttpServiceManager.getInstance().request(constant.forgotPassword, { email }, 'post').
            then((response) => {
                dispatch({
                    type: RESET_PASSWORD_SUCCESS,
                    payload: response
                });
                cbSuccess(response)
            }).catch((error) => {
                dispatch({
                    type: RESET_PASSWORD_FAIL,
                    payload: error
                });
                cbFailure(error)
            });
    }
}