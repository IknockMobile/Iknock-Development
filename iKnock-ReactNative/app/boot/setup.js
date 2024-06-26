import React, { Component } from "react";
import { StyleProvider, Root } from "native-base";

import AppNavigation from "../AppNavigation";
import getTheme from "../theme/components";
import variables from "../theme/variables/commonColor";
import { setNavigatorRef } from "../services/NavigationService";

export default class Setup extends Component {
    render() {
        return (
            <StyleProvider style={getTheme(variables)}>
                <Root>
                    <AppNavigation ref={ref => setNavigatorRef(ref)}/>
                </Root>
            </StyleProvider>
        );
    }
}