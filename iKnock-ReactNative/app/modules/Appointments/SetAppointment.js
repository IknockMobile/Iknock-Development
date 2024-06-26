import React, { Component } from 'react';
import { StyleSheet, TouchableOpacity } from 'react-native'
import { Container, View, ListItem, Text, Content, Textarea } from 'native-base';
import { SquareButton } from '../../reuseableComponents'
import colors from '../../assets/colors';
import { connect } from "react-redux";
import { setAppointmentNotAvailablity } from "../../actions";
import DateTimePicker from 'react-native-modal-datetime-picker';
import { Toasts } from "../../Utility/showToast";
import { getCurrDate } from "../../Utility";

class SetAppointment extends Component {

    state = {
        selectedStartDate: '',
        selectedEndDate: '',
        description: '',
        type: '',
        isDateTimePickerVisible: false,
    }

    componentDidUpdate(prevProps) {
        if (prevProps !== this.props) {
            if (this.props.error !== '') {
                Toasts.showToast(this.props.error);
            }
            if (this.props.message !== '') {
                Toasts.showToast(this.props.message, 'success');
                this.props.navigation.pop();
            }
        }
    }

    _showDateTimePicker = (type) =>
        this.setState({
            type: type,
            isDateTimePickerVisible: true
        });

    _hideDateTimePicker = () => this.setState({ isDateTimePickerVisible: false });

    _handleDatePicked = (date) => {
        let curr_date = date.getDate();
        let curr_month = date.getMonth() + 1;
        //Months are zero based    
        let curr_year = date.getFullYear();

        let curr_dateTime = curr_month + "-" + curr_date + "-" + curr_year + " " + date.getHours() + ":" + date.getMinutes()

        if (this.state.type === 'start') {
            this.setState({
                selectedStartDate: curr_dateTime
            });
        } else {
            this.setState({
                selectedEndDate: curr_dateTime
            });
        }
        this._hideDateTimePicker();
    };

    _onPressSubmitBtn = () => {
        const { selectedStartDate, selectedEndDate } = this.state
        if (selectedStartDate !== '' && selectedEndDate !== '') {
            this.props.setAppointmentNotAvailablity(selectedStartDate, selectedEndDate, this.state.description, true);
        } else {
            Toasts.showToast('Please Select Start and End Date.');
        }
    }
    render() {
        let date = getCurrDate();
        return (
            <Container>
                <Content>
                    <Text style={{
                        alignItems: 'center',
                        padding: 30,
                        textAlign: 'center',
                        color: colors.DarkBlue,
                        fontSize: 20
                    }}>
                        Please select a Date and time when you are not available.
                    </Text>
                    <TouchableOpacity onPress={() => this._showDateTimePicker('start')}>
                        <View style={styles.cellView}>
                            <Text>Start Date</Text>
                            <Text>{this.state.selectedStartDate}</Text>
                        </View>
                    </TouchableOpacity>
                    <TouchableOpacity onPress={() => this._showDateTimePicker('end')}>
                        <View style={styles.cellView}>
                            <Text>End Date</Text>
                            <Text>{this.state.selectedEndDate}</Text>
                        </View>
                    </TouchableOpacity>
                    <Textarea
                        rowSpan={7} bordered placeholder="Description"
                        value={this.props.resultTypeText}
                        style={{ margin: 15, backgroundColor: colors.LightGrey }}
                        onChangeText={(text) => this.setState({ description: text })} />
                    <ListItem noBorder>
                        <SquareButton
                            onPress={() => this._onPressSubmitBtn()}
                            title={'Submit'}
                        />
                    </ListItem>
                </Content>
                <DateTimePicker
                    mode='datetime'
                    isVisible={this.state.isDateTimePickerVisible}
                    onConfirm={this._handleDatePicked}
                    minimumDate={new Date(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes())}
                    onCancel={this._hideDateTimePicker}
                    is24Hour={false}
                />
            </Container >
        );
    }
}

const styles = StyleSheet.create({
    cellView: {
        flex: 1,
        flexDirection: 'row',
        justifyContent: 'space-between',
        padding: 20
    }
})
const mapStateToProps = (state) => {
    return {
        error: state.myAppointment.error,
        loading: state.myAppointment.loading,
        message: state.myAppointment.message,
    }
}
export default connect(mapStateToProps, {
    setAppointmentNotAvailablity,
})(SetAppointment);