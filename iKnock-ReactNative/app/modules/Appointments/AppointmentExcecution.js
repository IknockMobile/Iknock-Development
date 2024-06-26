import React, { Component } from 'react';
import { ImageBackground } from 'react-native';
import { Container, View, Text, ListItem, Left, Textarea, Content, List } from 'native-base';
import colors from '../../assets/colors';
import { SquareButton, SquareFullButtonWithIcon } from '../../reuseableComponents';
import { connect } from "react-redux";
import { myAppointmentExecution, onLeadAppointmentUpdate } from '../../actions';
import { SpinnerView } from '../../Utility/common/SpinnerView';
import { Toasts } from "../../Utility/showToast";
import styles from '../../assets/styles';
import { isImageNull } from "../../Utility";

class AppointmentExcecution extends Component {
    constructor(props) {
        super(props)
        this.state = {
            lead_id: '',
            appointment_id: '',
            appointment_date: '',
            media: {},
            status: {},
            type: {},
            index: 0,

        }
    }
    status = {
        appointement_result: ''
    }
    componentDidMount() {

        let navigation = this.props.navigation;
        let lead_detail = navigation.getParam('appointmentDetail');
        let index = navigation.getParam('index');

        this.setState({
            index,
            lead_id: lead_detail.id,

            appointement_result: lead_detail.appointment_result,

            appointment_id: lead_detail.appointment_id,
            appointment_date: lead_detail.appointment_date,
            media: lead_detail.media.length === 0 ? { path: '' } : lead_detail.media[0],
            status: lead_detail.status,
            type: lead_detail.type,
        });
    }
    onPressFollowUp = () => {
        this.props.navigation.navigate('followUpMarketing',
            {
                'lead_id': this.state.lead_id
            });
    }
    onPressDoneButton = () => {
        const { lead_id, appointment_id, appointement_result } = this.state
        this.props.myAppointmentExecution(lead_id, appointment_id, appointement_result,
            true, this.cbSuccess, this.cbFailer);
    }
    cbSuccess = (response) => {
        //for update appointment list item
        const { index } = this.state;

        if (index !== undefined) {
            this.props.onLeadAppointmentUpdate(index, response);
        }

        if (this.props.message !== '') {
            Toasts.showToast(this.props.message, 'success');

            setTimeout(() => {
                this.props.navigation.pop();
            }, 500);
        }
    }
    cbFailer = (error) => {
        Toasts.showToast(error);
    }

    render() {
        const { status, type, media, appointment_date, appointement_result } = this.state;
        return (
            <Container>
                <Content>
                    <ImageBackground style={{ width: '100%', height: 230 }} source={{ uri: isImageNull(media.path) }}>
                        <View style={{ flex: 1, justifyContent: 'flex-end' }}>
                            <View style={styles.transparatBg}>
                                <Text style={{ color: status === null ? '#fff' : status.color_code, fontWeight: 'bold', fontSize: 18, width: '45%' }}
                                    numberOfLines={1}
                                >
                                    {status === null ? 'not define' : status.title}
                                </Text>
                                <Text style={{ color: status === null ? '#fff' : status.color_code, fontWeight: 'bold', fontSize: 18, width: '45%', textAlign: 'right' }}
                                    numberOfLines={1}
                                >
                                    {type.title}
                                </Text>
                            </View>
                        </View>
                    </ImageBackground>
                    <List>
                        <ListItem noBorder>
                            <Left>
                                <Text>Actual Appointment Schedule</Text>
                            </Left>
                        </ListItem>
                        <SquareFullButtonWithIcon
                            onPress={this.props.statusListListDialog}
                            title={appointment_date}
                        />
                        <ListItem noBorder>
                            <Left>
                                <Text>Appointment Result</Text>
                            </Left>
                        </ListItem>

                        <Textarea
                            rowSpan={7} bordered placeholder="Type here your result"
                            value={appointement_result}
                            style={{ margin: 15, backgroundColor: colors.LightGrey }}
                            onChangeText={(text) =>
                                this.setState({ appointement_result: text })
                            } />
                        <ListItem noBorder>
                            <SquareButton
                                onPress={() => this.onPressDoneButton()}
                                title={'Done'}
                            />
                        </ListItem>
                        <ListItem noBorder>
                            <SquareButton
                                buttonStyle={{ minHeight: 45, margin: 0 }}
                                onPress={() => this.onPressFollowUp()}
                                title={'Schedule Follow Up Marketing'}
                            />
                        </ListItem>
                    </List>
                </Content>
                {this.props.loading && <SpinnerView />}
            </Container>
        );
    }
}

const mapStateToPrps = (state) => {
    return {
        resultTypeText: state.myAppointment.resultTypeText,
        myAppointmentList: state.myAppointment.myAppointmentList,
        error: state.myAppointment.error,
        refreshing: state.myAppointment.refreshing,
        loading: state.myAppointment.loading,
        nextPage: state.myAppointment.nextPage,
        currentPage: state.myAppointment.currentPage,
        message: state.myAppointment.message
    };
};
export default connect(mapStateToPrps, {
    myAppointmentExecution,
    onLeadAppointmentUpdate
})(AppointmentExcecution);