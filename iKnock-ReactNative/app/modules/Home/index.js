import React, { Component } from 'react';
import { Container, Tabs, Tab } from 'native-base';
import LeadsList from "./LeadsList";
import LeadsMap from "./LeadsMap";

export default class Home extends Component {
    render() {
        return (
            <Container>
                <Tabs locked={true}>
                    <Tab heading="Map View">
                        <LeadsMap navigation={this.props.navigation} />
                    </Tab>
                    <Tab heading="List View">
                        <LeadsList navigation={this.props.navigation} />
                    </Tab>
                </Tabs>
            </Container>
        );
    }
}