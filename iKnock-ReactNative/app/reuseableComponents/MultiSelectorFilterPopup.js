import React, { Component } from "react";
import { View, Image } from "react-native";
import { Container, Content, ListItem, Left, Body, Right, Text, List, Button, Item } from 'native-base';
import PopupDialog, { SlideAnimation, DialogTitle } from 'react-native-popup-dialog';
import Images from "../assets";
import styles from "../assets/styles";
import Colors from "../assets/colors";
import { Dropdowns, } from "./Dropdown";
import { MultiSelectValues } from "./MultiSelectValues";


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
class MultiSelectorFilterPopup extends Component {
    render() {

        return (
            <PopupDialog dialogStyle={{ width: '100%', height: '100%' }}
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
                                    onPress={() => this.props.onPrssApplyFilterButton()}
                                >
                                    <Text style={{ color: Colors.White }}>Apply</Text>
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
                                    />
                                      {/* <Dropdowns
                                        listItems={this.props.tenantUserList}
                                        selectedValue={this.props.selectedUser}
                                        onValueChange={this.props.onChangeUser.bind(this)}
                                        label={'All Users'}
                                        width={350}
                                        placeholder={'Select User'}
                                    />  */}
                                </ListItem>
                            }


                            <ListItem noBorder>
                                <Text style={{ fontWeight: 'bold' }}>Type</Text>
                            </ListItem>
                            <ListItem noBorder>
                                <Dropdowns
                                    listItems={this.props.leadTypeList}
                                    selectedValue={this.props.selectedTypes}
                                    onValueChange={this.props.onChangeType.bind(this)}
                                    label={'All Type'}
                                    width={350}
                                    placeholder={'Select Type'}
                                />
                            </ListItem>
                            <ListItem noBorder>
                                <Text style={{ fontWeight: 'bold' }}>Time Period</Text>
                            </ListItem>
                            <ListItem noBorder>
                                <Dropdowns
                                    listItems={radio_time_period}
                                    selectedValue={this.props.selectedTimePeriod}
                                    onValueChange={this.props.onChangeTimePeriod.bind(this)}
                                    label={'Select Time Period'}
                                    width={350}
                                    placeholder={'Select Time Period'}
                                />

                            </ListItem>
                            <ListItem noBorder>
                                <Text style={{ fontWeight: 'bold' }}>Status</Text>
                            </ListItem>
                            <ListItem noBorder>
                                <Dropdowns
                                    listItems={this.props.stateList}
                                    selectedValue={this.props.selectedStatus}
                                    onValueChange={this.props.onChangeStatus.bind(this)}
                                    label={'All Status'}
                                    width={350}
                                    placeholder={'Select Status'}
                                />
                            </ListItem>
                        </List>
                    </Content>
                </Container>
            </PopupDialog >
        );

    }
}
const slideAnimation = new SlideAnimation({
    slideFrom: 'bottom',
});

export { MultiSelectorFilterPopup };
