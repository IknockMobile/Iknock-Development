import React, { Component } from 'react';
import { FlatList, Alert } from 'react-native'
import { Container, View, ListItem, Left, Text, } from 'native-base';
import { DataPickerButton, SquareButton, InputFieldWithLabel } from '../../reuseableComponents'
import colors from '../../assets/colors';
import { connect } from "react-redux";
import { changedAppointment, changedDateTime } from "../../actions";
import { getCurrDate } from "../../Utility";

class SummaryAppointSchedule extends Component {

    constructor(props){
        super(props);
    this.state = {
        selectedDate: this.props.leadDetail.appointment_date?this.props.leadDetail.appointment_date:  'Select Date',
        isDateTimePickerVisible: false,
        model: [
            { key: 1, label: 'Name' },
            { key: 2, label: 'Age' }
        ],
    };
    console.log('this.pro',this.props)
}
    _showDateTimePicker = () => this.setState({ isDateTimePickerVisible: true });

    _hideDateTimePicker = () => this.setState({ isDateTimePickerVisible: false });

    _handleDatePicked = (date) => {

        let curr_date = date.getDate();
        let curr_month = date.getMonth() + 1;
        //Months are zero based    
        let curr_year = date.getFullYear();
        let curr_dateTime = curr_month + "-" + curr_date + "-" + curr_year + " " + date.getHours() + ":" + date.getMinutes()


        let currDate = getCurrDate();
        let systemDate = new Date(currDate.getFullYear(), currDate.getMonth(), currDate.getDate(), currDate.getHours(), currDate.getMinutes());
        console.log('systemDate :: ', systemDate);
        let systemDateWith30 = new Date(currDate.getFullYear(), currDate.getMonth(), currDate.getDate(), currDate.getHours(), currDate.getMinutes() + 30);
        let systemDateWith60 = new Date(currDate.getFullYear(), currDate.getMonth(), currDate.getDate(), currDate.getHours(), currDate.getMinutes() + 60);
        console.log('systemDateWith60 :: ', systemDateWith60);
        let userSelectedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes());

        if (systemDateWith60 < userSelectedDate && systemDate < userSelectedDate) {
            this.setState({
                selectedDate: curr_dateTime
            });
            this.props.changedDateTime(curr_dateTime);
        } else {
            // setTimeout(() => {
            //     Alert.alert('Alert', "Appointment can be scheduled only after 30 minutes from the current time.");
            // }, 500);
            setTimeout(() => {
                Alert.alert('Alert', "Appointment can be scheduled only after 1 hour from the current time.");
            }, 500);
            //show error message
        }
        this._hideDateTimePicker();
    };
    render() {
        const { selectedDate } = this.state;
        return (
            <Container>
                <View>
                    <FlatList
                        scrollEnabled={false}
                        extraData={this.props.queryAppointment}
                        data={this.props.queryAppointment}
                        renderItem={({ item, index }) => (
                            <InputFieldWithLabel
                                //onChangeText={this.onChangeValue}
                                onChangeText={(text) => this.props.changedAppointment(text, item.query_id)}
                                id={item.id}
                                index={index}
                                query={item.query}
                                response={item.response}
                            />
                        )}
                        keyExtractor={(item, index) => item.key + "-" + index}
                    />

                    {/* <RoundInputField
                                placeholder={'Enter Name of person you talk to below'} />
                            <RoundInputField
                                placeholder={'Enter Phone # of person you talk to below'} />
                            <RoundInputField
                                placeholder={'Enter Email of person you talk to below'} />
                            <RoundInputField
                                placeholder={'Home Observation Entered Here'} /> */}

                    <ListItem noBorder>
                        <Left>
                            <Text>Set Appointment Schedule</Text>
                        </Left>
                    </ListItem>
                    <DataPickerButton
                        onPress={this._showDateTimePicker}
                        isVisible={this.state.isDateTimePickerVisible}
                        onConfirm={this._handleDatePicked}
                        onCancel={this._hideDateTimePicker}
                        selectedDate={selectedDate}
                        backgroundColor={colors.DarkBlue}
                        textColor={colors.White}
                        iconColor={colors.White}
                        // placeHolderText={this.state.chosenDate}
                        onDateChange={this.setDate} />
                    <ListItem noBorder>
                        <SquareButton
                            onPress={() => {
                                this.props.leadDetail.appointment_date?this.props.onPressAppointQuerySubmitBtn(1):
                                this.props.onPressAppointQuerySubmitBtn()
                            }}
                            title={'Submit'}
                        />
                    </ListItem>
                </View>
            </Container>
        );
    }
}
const mapStateToProps = (state) => {
    return {
        leadDetail: state.summery.leadDetail,
        queryAppointment: state.summery.queryAppointment,
    }
}
export default connect(mapStateToProps, {
    changedAppointment,
    changedDateTime
})(SummaryAppointSchedule);