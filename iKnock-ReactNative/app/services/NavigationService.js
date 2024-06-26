//
//  NavigationService.js:
//  BoilerPlate
//
//  Created by Retrocube on 10/4/2019, 9:35:30 AM.
//  Copyright © 2019 Retrocube. All rights reserved.
//
import {
    NavigationActions,
    StackActions,
} from "react-navigation";
import { DrawerActions } from 'react-navigation-drawer';
let navigatorRef;

const setNavigatorRef = ref => (navigatorRef = ref);
const getNavigatorRef = () => navigatorRef;

const push = (routeName, params = {}) =>
    navigatorRef.dispatch(NavigationActions.navigate({ routeName, params }));

const pop = (popCount = 1, params = {}) =>
    navigatorRef.dispatch(
        StackActions.pop(({ n: popCount, params }))
    );

const popToTop = () => navigatorRef.dispatch(StackActions.popToTop());
const reset = (routeName, params = {}) => {
    const actionToDispatch = StackActions.reset({
        index: 1,
        actions: [
            NavigationActions.navigate({ routeName: "drawerScreenStack" }),
            NavigationActions.navigate({ routeName, params })
        ],
    });
    navigatorRef.dispatch(actionToDispatch);
};

const openDrawer = () => navigatorRef.dispatch(DrawerActions.openDrawer());
const closeDrawer = () => navigatorRef.dispatch(DrawerActions.closeDrawer());
const toggleDrawer = () => navigatorRef.dispatch(DrawerActions.toggleDrawer());

export {
    setNavigatorRef,
    getNavigatorRef,
    push,
    pop,
    openDrawer,
    closeDrawer,
    toggleDrawer,
    popToTop,
    reset
};
