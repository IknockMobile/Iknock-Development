import React, { Component } from "react";
import { Image, TouchableOpacity } from 'react-native';
import { Button, Icon } from "native-base";
import { createAppContainer, createSwitchNavigator } from "react-navigation";
import { createStackNavigator } from "react-navigation-stack";
import { createDrawerNavigator, DrawerActions } from "react-navigation-drawer";

import Colors from './assets/colors';
import Images from './assets';
import styles from './assets/styles';

import Authentication from "./modules/Authentication";
import Login from "./modules/Authentication/Login";
import ForgotPassword from "./modules/Authentication/ForgotPassword";

import Home from "./modules/Home"
import LeadsList from "./modules/Home/LeadsList";
import MyLeadTabs from "./modules/Home/MyLeadTabs";
import Reports from './modules/Reports';

import ReportsTableView from './modules/ReportsTableView';
import CommsionReports from './modules/CommsionReports';


import Summary from "./modules/Summary";
import StatusHistory from "./modules/StatusHistory";

import Appointments from "./modules/Appointments";
import AppointmentExcecution from "./modules/Appointments/AppointmentExcecution";
import FollowUpMarketing from "./modules/Appointments/FollowUpMarketing";
import Calenders from "./modules/Appointments/Calenders";
import SetAppointment from "./modules/Appointments/SetAppointment";

import Training from "./modules/Training";
import TrainerPdfViewer from "./modules/TrainerPdfViewer";
import OwnerIntroduction from "./modules/OwnerIntroduction";

import Sidebar from "./modules/Sidebar";
import WebViews from "./modules/WebViews";
import { toggleDrawer } from "./services/NavigationService";

//drawer
const DrawerScreenStack = createStackNavigator(
    {
        home: {
            screen: Home,
            navigationOptions: { title: 'All Leads' }
        },
        myLeadTabs: {
            screen: MyLeadTabs,
            navigationOptions: ({ navigation }) => ({
                title: 'My Assigned Leads',
            }),
        },
        myAppointment: {
            screen: Appointments,
            navigationOptions: ({ navigation }) => ({
                title: 'My Appointments',
            }),
        },
        // reports: {
        //     screen: Reports,
        //     navigationOptions: ({ navigation }) => ({
        //         title: 'Reports',
        //     }),
        // },
        reportsTableView: {
            screen: ReportsTableView,
            navigationOptions: ({ navigation }) => ({
                title: 'Reports',
            }),
        },
        commsionReports: {
            screen: CommsionReports,
            navigationOptions: ({ navigation }) => ({
                title: 'Commission Reports',
            }),
        },
        calender: {
            screen: Calenders,
            navigationOptions: ({ navigation }) => ({
                title: 'Calendar',
                headerRight: (
                    <TouchableOpacity
                        onPress={() => navigation.navigate("setAppointment")}>
                        <Image style={styles.iconRight} source={Images.ic_add} />
                    </TouchableOpacity>
                )
            }),
        },
        training: {
            screen: Training,
            navigationOptions: ({ navigation }) => ({
                title: 'Training',
            }),
        },
    },
    {
        headerMode: "float",
        defaultNavigationOptions: ({ navigation }) => ({
            headerStyle: { backgroundColor: Colors.colorPrimary },
            headerTitleStyle: { color: Colors.White },
            headerLeft: (
                <TouchableOpacity
                    onPress={() => navigation.dispatch(DrawerActions.toggleDrawer())}
                    style={{height:80,justifyContent:'center',paddingLeft:25}}
                >
                    <Image style={{width: 26,height: 26,}} source={Images.toggle} />
                </TouchableOpacity>
            ),
        })
    }
);
const DrawerStack = createDrawerNavigator(
    {
        drawerScreenStack: { screen: DrawerScreenStack },
    },
    {
        contentComponent: Sidebar,
        disableGestures: true,
        drawerType: 'front',
        drawerLockMode: 'locked-closed',
        edgeWidth: -100,
        drawerWidth: 300,
    },
)

const LoginStack = createStackNavigator(
    {
        login: {
            screen: Login,
            navigationOptions: () => ({
                header: null
            })
        },
        forgotPassword: {
            screen: ForgotPassword,
        }
    },
    {
        defaultNavigationOptions: ({ navigation }) => ({
            headerStyle: { backgroundColor: Colors.colorPrimary },
            headerBackImage: <Image source={Images.ic_back}
                style={{ width: 26, height: 26, }} />

        }),
    }
);


const PrimaryStack = createStackNavigator(
    {
        drawerStack: {
            screen: DrawerStack,

            navigationOptions: ({ navigation }) => ({
                header: null
                // headerStyle: { backgroundColor: Colors.colorPrimary },
                // headerTitleStyle: { color: Colors.White },
                // headerLeft: (
                //     <TouchableOpacity
                //         onPress={() => navigation.dispatch(DrawerActions.toggleDrawer())}
                //     >
                //         <Image style={styles.toggleSize} source={Images.toggle} />
                //     </TouchableOpacity>
                // ),
            })
        },
        summary: {
            screen: Summary,
        },
        propertyList: {
            screen: LeadsList,
            navigationOptions: ({ navigation }) => ({
                title: 'List'
            })
        },
        statusHistory: {
            screen: StatusHistory,
            navigationOptions: () => ({
                title: 'Status History'
            })
        },
        appointmentExcecution: {
            screen: AppointmentExcecution,
            navigationOptions: () => ({
                title: 'Appointment Execution'
            })
        },
        followUpMarketing: {
            screen: FollowUpMarketing,
            navigationOptions: () => ({
                title: 'Follow Up Marketing'
            })
        },
        ownerIntroduction: {
            screen: OwnerIntroduction,
        },
        trainerPdfViewer: {
            screen: TrainerPdfViewer,
            navigationOptions: () => ({
                title: 'Owner Introduction'
            })
        },
        webViews: {
            screen: WebViews,
            header: null
        },
        setAppointment: {
            screen: SetAppointment,
            navigationOptions: () => ({
                title: ''
            })
        }
    },
    {
        defaultNavigationOptions: ({ navigation }) => ({
            gesturesEnabled: false,
            headerStyle: { backgroundColor: Colors.colorPrimary, },
            headerTitleStyle: { color: Colors.White },
            headerBackImage:
                <Image source={Images.back_arrow_white}
                    style={{ width: 26, height: 26 }} />

        })
    }
);


const rootNavigator = createAppContainer(
    createSwitchNavigator({
        authentication: {
            screen: Authentication,
        },
        loginStack: {
            screen: LoginStack,
        },
        primaryStack: {
            screen: PrimaryStack,
        },
    },
        {
            initialRouteName: "authentication"
        }
    )
);

export default rootNavigator;