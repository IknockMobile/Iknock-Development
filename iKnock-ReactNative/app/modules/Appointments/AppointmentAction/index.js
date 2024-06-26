import {
    GET_MY_APPOINTMENT,
    GET_MY_APPOINTMENT_FAIL,
    GET_MY_APPOINTMENT_SUCCESS,

    MY_APPOINTMENT_REFRESHING,
    MY_APPOINTMENT_LOADING,

    GET_MY_APPOINTMENT_EXECUTION_SUCCESS,
    GET_MY_APPOINTMENT_EXECUTION_FAIL,

    EMAIL_DATE_TIME_CHANGED,
    PHONE_DATE_TIME_CHANGED,
    GET_FOLLOW_UP_SUCCESS,
    GET_FOLLOW_UP_FAIL,

    GET_MAIL_TEMPLETE_SUCCESS,
    GET_MAIL_TEMPLETE_FAIL,

    GET_TODAY_APPOINTMENT,
    GET_TODAY_APPOINTMENT_SUCCESS,
    GET_TODAY_APPOINTMENT_FAIL,

    SET_APPOINTMENT_SUCCESS,
    SET_APPOINTMENT_FAIL,

    GET_MONTHLY_APPOINTMENT,
    GET_MONTHLY_APPOINTMENT_SUCCESS,
    GET_MONTHLY_APPOINTMENT_FAIL
} from '../../../actions/types';
import constant from '../../../HttpServiceManager/constant';
import httpServiceManager from '../../../HttpServiceManager/HttpServiceManager';

export const onLeadAppointmentUpdate = (index, item) => {
    return (dispatch) => {
        dispatch({
            type: "ON_LEAD_APPOINTMENT_UPDATE",
            index,
            payload: item
        });
    }
}

export const getMyAppointment = (page, is_refreshing = false, query = "", appointment_date = "", is_loading = false, ) => {
    let params;
    if (appointment_date !== '') {
        params = {
            is_out_bound: "0",
            page,
            search: query,
            appointment_date
        }
    } else {
        params = {
            is_out_bound: "0",
            page,
            search: query,
            // appointment_date
        }
    }
    return (dispatch) => {
        if (is_refreshing) {
            dispatch({
                type: MY_APPOINTMENT_REFRESHING,
            });
        }

        if (is_loading) {
            dispatch({
                type: MY_APPOINTMENT_LOADING,
            });
        }
        httpServiceManager.getInstance().request(constant.getMyappointment, params, 'get').
            then((response) => {
                if (is_refreshing) {
                    dispatch({
                        type: GET_MY_APPOINTMENT_SUCCESS,
                        payload: response
                    });
                } else {
                    dispatch({
                        type: GET_MY_APPOINTMENT,
                        payload: response
                    });
                }
            }).catch((error) => {
                dispatch({
                    type: GET_MY_APPOINTMENT_FAIL,
                    payload: error
                });
            });
    }
}

//get today appointment list
export const getTodayAppointment = (page, is_refreshing = false, appointment_date = "") => {

    let url = constant.getMyappointment + "?page=" + page

    if (appointment_date !== '') {
        url = constant.getMyappointment + "?page=" + page + '&appointment_date=' + appointment_date;
    }

    console.log('URL getTodayAppointment :: ', url)

    return (dispatch) => {
        if (is_refreshing) {
            dispatch({
                type: MY_APPOINTMENT_REFRESHING,
            });
        }

        httpServiceManager.getInstance().request(url, '', 'get').
            then((response) => {
                if (is_refreshing) {
                    dispatch({
                        type: GET_TODAY_APPOINTMENT_SUCCESS,
                        payload: response
                    });
                } else {
                    dispatch({
                        type: GET_TODAY_APPOINTMENT,
                        payload: response
                    });
                }
            }).catch((error) => {
                dispatch({
                    type: GET_TODAY_APPOINTMENT_FAIL,
                    payload: error
                });
            });
    }
}

//get Monthly appointment list
export const getMonthlyAppointment = (page, is_refreshing = false, appointment_date = "") => {

    let url = constant.getMyappointment + "?page=" + page

    if (appointment_date !== '') {
        url = constant.getMyappointment + "?page=" + page + '&appointment_date=' + appointment_date;
    }

    console.log('URL getMonthlyAppointment : ', url);

    return (dispatch) => {
        if (is_refreshing) {
            dispatch({
                type: MY_APPOINTMENT_REFRESHING,
            });
        }

        httpServiceManager.getInstance().request(url, '', 'get').
            then((response) => {
                if (is_refreshing) {
                    dispatch({
                        type: GET_MONTHLY_APPOINTMENT_SUCCESS,
                        payload: response
                    });
                } else {
                    dispatch({
                        type: GET_MONTHLY_APPOINTMENT,
                        payload: response
                    });
                }
            }).catch((error) => {
                dispatch({
                    type: GET_MONTHLY_APPOINTMENT_FAIL,
                    payload: error
                });
            });
    }
}

export const myAppointmentExecution = (lead_id, appointment_id, result, is_loading = false, cbSuccess, cbFailer) => {

    let url = constant.userLeadAppointmentExecute

    let params = {
        "lead_id": lead_id,
        "appointment_id": appointment_id,
        "result": result,
    }
    return (dispatch) => {
        if (is_loading) {
            dispatch({
                type: MY_APPOINTMENT_LOADING,
            });
        }
        httpServiceManager.getInstance().request(url, params, 'post').
            then((response) => {

                dispatch({
                    type: GET_MY_APPOINTMENT_EXECUTION_SUCCESS,
                    payload: response
                });
                cbSuccess(response.data);//call Back
            }).catch((error) => {
                dispatch({
                    type: GET_MY_APPOINTMENT_EXECUTION_FAIL,
                    payload: error
                });
                cbFailer(error);//call Back
            });
    }
}

export const getMailTemlete = (is_loading = false) => {
    let url = constant.marketingTemplateList
    return (dispatch) => {
        if (is_loading) {
            dispatch({
                type: MY_APPOINTMENT_LOADING,
            });
        }
        httpServiceManager.getInstance().request(url, '', 'get').
            then((response) => {
                dispatch({
                    type: GET_MAIL_TEMPLETE_SUCCESS,
                    payload: response
                });

            }).catch((error) => {
                dispatch({
                    type: GET_MAIL_TEMPLETE_FAIL,
                    payload: error
                });
            });
    }
}

export const emailDateTimeChanged = (text) => {
    return {
        type: EMAIL_DATE_TIME_CHANGED,
        payload: text
    }
}
export const phoneDateTimeChanged = (text) => {
    return {
        type: PHONE_DATE_TIME_CHANGED,
        payload: text
    }
}

export const scheduleFollowUpMarketing = (lead_id, mail_appointment_date, phone_appointment_date, template_id, is_loading = false, ) => {

    let url = constant.userMarketingAppointmentCreate

    let params = {
        "lead_id": lead_id,
        "mail_appointment_date": mail_appointment_date,
        "phone_appointment_date": phone_appointment_date === 'Select Date and Time' ? '' : phone_appointment_date,
        "template_id": template_id

    }
    return (dispatch) => {
        if (is_loading) {
            dispatch({
                type: MY_APPOINTMENT_LOADING,
            });
        }
        httpServiceManager.getInstance().request(url, params, 'post').
            then((response) => {
                dispatch({
                    type: GET_FOLLOW_UP_SUCCESS,
                    payload: response
                });

            }).catch((error) => {
                dispatch({
                    type: GET_FOLLOW_UP_FAIL,
                    payload: error
                });
            });
    }
}


export const setAppointmentNotAvailablity = (start_date, end_date, description, is_loading = false, ) => {

    let url = constant.userOutboundAppointmentCreate;
    let params = {
        "start_date": start_date,
        "end_date": end_date,
        "result": description
    }
    return (dispatch) => {
        if (is_loading) {
            dispatch({
                type: MY_APPOINTMENT_LOADING,
            });
        }
        httpServiceManager.getInstance().request(url, params, 'post').
            then((response) => {
                dispatch({
                    type: SET_APPOINTMENT_SUCCESS,
                    payload: response
                });

            }).catch((error) => {
                dispatch({
                    type: SET_APPOINTMENT_FAIL,
                    payload: error
                });
            });
    }
}

