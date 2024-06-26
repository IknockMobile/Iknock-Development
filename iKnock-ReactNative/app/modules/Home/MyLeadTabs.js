import React, { Component } from 'react';
import { Container, Tabs, Tab } from 'native-base';
import MyLeadList from "./MyLeadList";
import MyLeadMap from "./MyLeadMap";

export default class MyLeadTabs extends Component {
    render() {
        return (
            <Container>
                <Tabs locked={true}>
                    <Tab heading="Map View">
                        <MyLeadMap navigation={this.props.navigation}
                            type='own' />
                    </Tab>
                    <Tab heading="List View">
                        <MyLeadList navigation={this.props.navigation} />
                    </Tab>
                </Tabs>
            </Container>
        );
    }
}