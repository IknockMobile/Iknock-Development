import React, { Component } from "react";
import { View, Image } from "react-native";
import { Container, Content, ListItem, Left, Body, Right, Text, List, Button } from 'native-base';
import PopupDialog, { SlideAnimation, DialogTitle } from 'react-native-popup-dialog';
import Images from "../assets";
import styles from "../assets/styles";
import Colors from "../assets/colors";
import { Dropdowns } from "./Dropdown";
import { MultiSelectValues } from "./MultiSelectValues";
import colors from "../assets/colors";

import { showMessage } from "../Utility";

var radio_time_period = [
    { name: 'Today', id: 'today' },
    { name: 'Yesterday', id: 'yesterday' },
    { name: 'This Week', id: 'week' },
    { name: 'Last Week', id: 'last_week' },
    { name: 'This Month', id: 'month' },
    { name: 'Last Month', id: 'last_month' },
    { name: 'This Year', id: 'year' },
    { name: 'Last Year', id: 'last_year' },
];
var radio_show_amount = [
    { name: 'Percentage', id: 'percentage' },
    { name: 'Dollar', id: 'amount' },
];
class FilterPopup extends Component {

    state = {
        selectedUsers: [],
        selectedTypes: [],
        selectedTimePeriod: "",
        selectedStatus: []
    }


    onChangeUser = selectedUsers => this.setState({ selectedUsers });

    onChangeType = selectedTypes => this.setState({ selectedTypes });

    onChangeStatus = selectedStatus => this.setState({ selectedStatus });

    onChangeTimePeriod = selectedTimePeriod => this.setState({ selectedTimePeriod });

    onPrssApplyFilterButton = () => {
        const { selectedUsers, selectedTypes, selectedTimePeriod, selectedStatus } = this.state;
        // if (selectedUsers.length || selectedTypes.length || selectedTimePeriod.length || selectedStatus.length) {

        this.props.onChangeUser(selectedUsers);
        this.props.onChangeType(selectedTypes);
        this.props.onChangeTimePeriod(selectedTimePeriod)
        this.props.onChangeStatus(selectedStatus);
        this.props.onPrssApplyFilterButton();

        // } else {
        //     showMessage('Alert', 'You have not applied any filter.')
        // }
    }


    onPrssClearAllFilter = () => {

        showMessage('Alert', 'Are you sure you would like to clear your current filter settings?',
            () => {
                this.setState({
                    selectedUsers: [],
                    selectedTypes: [],
                    selectedTimePeriod: "",
                    selectedStatus: []
                });
                this.props.onPrssClearAllFilter();
            })

    }
    render() {
        const { selectedUsers, selectedTypes, selectedTimePeriod, selectedStatus } = this.state;
        return (
            <PopupDialog
                dialogStyle={{ width: '100%', height: '100%' }}
                ref={this.props.setRef}
                dialogAnimation={slideAnimation} >
                <Container>
                    <View style={{ backgroundColor: Colors.DarkBlue, }}>
                        <ListItem icon noBorder>
                            <Left>
                                <Button transparent
                                    onPress={() => this.props.onPressCancelButton()}
                                >
                                    <Image style={styles.toggleSize} source={Images.back_arrow_white} />
                                </Button>
                            </Left>
                            <Body>
                                <Text style={{ color: Colors.White, fontSize: 20, textAlign: 'center' }}>Filter</Text>
                            </Body>
                            <Right>
                                <Button transparent
                                    onPress={this.onPrssClearAllFilter}>

                                    <Text style={{ color: Colors.White }}>CLEAR ALL</Text>


                                </Button>
                            </Right>
                        </ListItem>
                    </View>
                    <Content>
                        <List>
                            <ListItem noBorder>
                                <Text style={{ fontWeight: 'bold' }}>Search By</Text>
                            </ListItem>
                            {this.props.isUserList &&
                                <ListItem noBorder>

                                    <MultiSelectValues
                                        listItems={this.props.tenantUserList}
                                        selectedItems={selectedUsers}
                                        onSelectedItemsChange={this.onChangeUser}
                                        InputPlaceholder={'Search User'}
                                        type={false}
                                    />
                                    {/* <Dropdowns
                                        listItems={this.props.tenantUserList}
                                        selectedValue={this.props.selectedUser}
                                        onValueChange={this.props.onChangeUser.bind(this)}
                                        label={'All Users'}
                                        width={350}
                                        placeholder={'Select User'}
                                    /> */}
                                </ListItem>
                            }


                            <ListItem noBorder>
                                <Text style={{ fontWeight: 'bold' }}>Type</Text>
                            </ListItem>
                            <ListItem noBorder>
                                <MultiSelectValues
                                    listItems={this.props.leadTypeList}
                                    selectedItems={selectedTypes}
                                    onSelectedItemsChange={this.onChangeType}
                                    InputPlaceholder={'Search Lead Type'}
                                    type={true}
                                />
                                {/* <Dropdowns
                                    listItems={this.props.leadTypeList}
                                    selectedValue={this.props.selectedTypes}
                                    onValueChange={this.props.onChangeType.bind(this)}
                                    label={'All Type'}
                                    width={350}
                                    placeholder={'Select Type'}
                                /> */}
                            </ListItem>
                            <ListItem noBorder>
                                <Text style={{ fontWeight: 'bold' }}>Time Period</Text>
                            </ListItem>
                            <ListItem noBorder>
                                <Dropdowns
                                    listItems={radio_time_period}
                                    selectedValue={selectedTimePeriod}
                                    onValueChange={this.onChangeTimePeriod}
                                    label={'Select Time Period'}
                                    width={350}
                                    placeholder={'Select Time Period'}
                                />

                            </ListItem>
                            <ListItem noBorder>
                                <Text style={{ fontWeight: 'bold' }}>Status</Text>
                            </ListItem>
                            <ListItem noBorder>
                                <MultiSelectValues
                                    listItems={this.props.stateList}
                                    selectedItems={selectedStatus}
                                    onSelectedItemsChange={this.onChangeStatus}
                                    InputPlaceholder={'Search Status'}
                                    type={true}
                                />
                                {/* <Dropdowns
                                    listItems={this.props.stateList}
                                    selectedValue={this.props.selectedStatus}
                                    onValueChange={this.props.onChangeStatus.bind(this)}
                                    label={'All Status'}
                                    width={350}
                                    placeholder={'Select Status'}
                                /> */}
                            </ListItem>
                        </List>
                    </Content>

                    <Button block style={{
                        margin: 24,
                        minHeight: 50,
                        backgroundColor: colors.btnDarkBlueBgColor
                    }}
                        onPress={this.onPrssApplyFilterButton} >
                        <Text style={{ color: colors.White }} uppercase={false}> {'Apply Filters'} </Text>

                    </Button>
                </Container>
            </PopupDialog >
        );
    }
}
const slideAnimation = new SlideAnimation({
    slideFrom: 'bottom',
});

export { FilterPopup };