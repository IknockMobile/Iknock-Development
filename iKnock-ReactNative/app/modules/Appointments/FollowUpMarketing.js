import React, { Component } from 'react';
import { FlatList, Alert } from 'react-native'
import { Container, Content, ListItem, View } from 'native-base';
import colors from '../../assets/colors';
import { DataPickerButton, FullButton, SquareButton, SquareGeryButton, SquareButtonWithIcon } from '../../reuseableComponents';
import PopupDialog, { SlideAnimation } from 'react-native-popup-dialog';
import { emailDateTimeChanged, phoneDateTimeChanged, scheduleFollowUpMarketing, getMailTemlete } from '../../actions';
import { connect } from 'react-redux';
import { SpinnerView } from '../../Utility/common/SpinnerView';
import { Toasts } from "../../Utility/showToast";

class FollowUpMarketing extends Component {

    state = {
        lead_id: '',
        isDateTimePickerVisible: false,
        type: '',

        selectedMailTitle: 'Select E-Mail Template',
        selectedMailId: -1,
    }
    componentDidMount() {
        let navigation = this.props.navigation;
        let lead_id = navigation.getParam('lead_id');
        this.setState({
            lead_id
        });
        this.props.getMailTemlete(true);
    }
    componentDidUpdate() {
        if (this.props.message !== '') {
            Toasts.showToast(this.props.message, 'success');
            this.props.navigation.pop();
        }
        if (this.props.error !== '') {
            Toasts.showToast(this.props.error);
        }
    }
    _showDateTimePicker = (type) => {
        this.setState({
            isDateTimePickerVisible: true,
            type: type
        });
    }

    _hideDateTimePicker = () => this.setState({ isDateTimePickerVisible: false });

    _handleDatePicked = (date) => {
        let curr_date = date.getDate();
        let curr_month = date.getMonth() + 1;
        //Months are zero based    
        let curr_year = date.getFullYear();

        if (this.state.type === 'email') {
            this.props.emailDateTimeChanged(curr_year + "-" + curr_month + "-" + curr_date + " " + date.getHours() + ":" + date.getMinutes())
        } else {
            this.props.phoneDateTimeChanged(curr_year + "-" + curr_month + "-" + curr_date + " " + date.getHours() + ":" + date.getMinutes())
        }
        this._hideDateTimePicker();
    };

    onPressMailButton = () => {
        this.popupDialog.show();
    }
    onItemSelect = (item) => {
        this.setState({
            selectedMailTitle: item.hint,
            selectedMailId: item.id
        });
        this.popupDialog.dismiss();
    }

    //for email
    onPressPromoteButton = () => {

        const { lead_id, selectedMailId } = this.state;
        if (selectedMailId === -1) {
            Toasts.showToast("Please select mail template");
        } else {
            const { selectedMailDateTime } = this.props;
            this.props.scheduleFollowUpMarketing(lead_id, selectedMailDateTime, '', selectedMailId, true);
        }
    }
    //for phone
    onPressPhonePromoteButton = () => {
        const { selectedPhoneDateTime } = this.props;
        if (selectedPhoneDateTime !== 'Select Date and Time') {
            this.props.scheduleFollowUpMarketing(this.state.lead_id, '', selectedPhoneDateTime, '', true);
        } else {
            Alert.alert('Please select date and time')
        }
    }
    render() {
        return (
            <Container>
                <Content>
                    <FullButton
                        buttonStyle={{ marginTop: 10 }}
                        title={"Schedule for Meeting"} />

                    <View style={{ margin: 16 }}>
                        <SquareButtonWithIcon
                            onPress={() => this.onPressMailButton()}
                            title={this.state.selectedMailTitle}
                            backgroundColor={colors.btnDarkBlueBgColor}
                        />
                    </View>

                    <DataPickerButton
                        onPress={() => this._showDateTimePicker('email')}
                        isVisible={this.state.isDateTimePickerVisible}
                        onConfirm={this._handleDatePicked}
                        onCancel={this._hideDateTimePicker}
                        selectedDate={this.props.selectedMailDateTime}

                        backgroundColor={colors.LightGrey}
                        textColor={colors.DarkBlueTextColor}
                        iconColor={colors.DarkBlue}
                        onDateChange={this.setDate} />

                    <SquareButton
                        buttonStyle={{ margin: 16, marginTop: 20 }}
                        onPress={() => this.onPressPromoteButton()}
                        title={"Send Email"} />


                    <View style={{ marginTop: 30 }}>

                        <FullButton
                            buttonStyle={{ marginVertical: 16, }}
                            title={"Schedule for Phone Call"} />

                        <DataPickerButton
                            onPress={() => this._showDateTimePicker('phone')}
                            isVisible={this.state.isDateTimePickerVisible}
                            selectedDate={this.props.selectedPhoneDateTime}
                            onConfirm={this._handleDatePicked}
                            onCancel={this._hideDateTimePicker}

                            backgroundColor={colors.LightGrey}
                            textColor={colors.DarkBlueTextColor}
                            iconColor={colors.DarkBlue}
                            onDateChange={this.setDate} />
                    </View>

                    <SquareButton
                        buttonStyle={{ margin: 16, marginTop: 20 }}
                        onPress={() => this.onPressPhonePromoteButton()}
                        title={"Promote Marketing Touch Follow Up"} />

                </Content>
                {/* Dialog */}
                <PopupDialog dialogStyle={{ width: '95%', height: '65%' }}
                    ref={(popupDialog) => { this.popupDialog = popupDialog; }}
                    dialogAnimation={slideAnimation}>
                    <FlatList
                        style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}
                        data={this.props.mailTemplete}
                        extraData={this.props.mailTemplete}
                        renderItem={({ item }) => (
                            <View style={{ margin: 5 }}>
                                <SquareGeryButton
                                    onPress={() => this.onItemSelect(item)}
                                    title={item.hint}
                                />
                            </View>
                        )}
                        keyExtractor={(item, index) => item.id + "-" + index}
                    />
                </PopupDialog>
                {this.props.loading && <SpinnerView />}
            </Container>
        );
    }
}
const slideAnimation = new SlideAnimation({
    slideFrom: 'bottom',
});
const mapStateToPrps = (state) => {
    return {
        error: state.myAppointment.error,
        refreshing: state.myAppointment.refreshing,
        loading: state.myAppointment.loading,
        message: state.myAppointment.message,
        mailTemplete: state.myAppointment.mailTemplete,

        selectedMailDateTime: state.myAppointment.selectedMailDateTime,
        selectedPhoneDateTime: state.myAppointment.selectedPhoneDateTime,
    };
};
export default connect(mapStateToPrps, {
    emailDateTimeChanged,
    phoneDateTimeChanged,
    scheduleFollowUpMarketing,
    getMailTemlete
})(FollowUpMarketing);