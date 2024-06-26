import AuthReducer from '../modules/Authentication/AuthReducer';
import LeadsReducer from '../modules/Home/HomeReducer';
import SummaryReducer from "./SummaryReducer";
import StatusHistoryReducer from "./StatusHistoryReducer";
import MyAppointmentReducer from "../modules/Appointments/AppointmentReducer";
import TrainerReducer from "./TrainerReducer";
import ReportReducer from "./ReportReducer";

import { combineReducers } from 'redux';

export default combineReducers({
    auth: AuthReducer,
    leads: LeadsReducer,
    summery: SummaryReducer,
    statusHistory: StatusHistoryReducer,
    myAppointment: MyAppointmentReducer,
    report: ReportReducer,
    trainers: TrainerReducer

})