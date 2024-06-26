import React, { Component } from 'react';
import {
    View,
    FlatList,
    Image,
} from 'react-native';
import { Calendar } from 'react-native-calendars';
import colors from '../../assets/colors';
import Images from '../../assets';
import { Container, Content, Card, CardItem, ListItem, Body, Right, Text } from 'native-base';
import { connect } from "react-redux";
import { getMonthlyAppointment, getTodayAppointment } from "../../actions";
import { SpinnerView } from '../../Utility/common/SpinnerView';
import styles from '../../assets/styles';
import { dateFormate } from "../../Utility";
import moment from 'moment/moment';

class Calenders extends Component {
    state = {
        type: '',
        seletedDate: '',
        previousMonth: '',
        nextMonth: '',
        obj: {}
    }
   
    componentDidMount = () => {
        var fullYear = new Date().getFullYear();
        var month = new Date().getMonth() + 1;
        //on back refresh
        this.didFocusListener = this.props.navigation.addListener(
            'didFocus',
            () => {
                this.props.getMonthlyAppointment(1, true, month + '-' + fullYear);
            },
        );
    }
    onChangeMonth = (date) => {
        this.setState({
            type: 'month',
            seletedDate: date
        });
        this.props.getMonthlyAppointment(1, true, date);
    }
    onPressDay = (date) => {
        this.setState({
            type: 'day',
            seletedDate: date
        });
        this.props.getTodayAppointment(1, true, date);
    }
    OnItemPressed = (item) => {
        if (item.id !== "") {
            this.props.navigation.navigate('appointmentExcecution',
                {
                    'appointmentDetail': item
                });
        }
    }
    ListEmptyView = () => {
        return (
            <View style={styles.message}>
                <Text style={{ textAlign: 'center' }}>
                    {
                        this.props.refreshing === true ? 'Loading...' : 'No Record Found.'
                    }
                </Text>
            </View>
        );
    }
    renderSeperator = () => {
        return (
            <View
                style={{
                    height: 1,
                    width: "100%",
                    backgroundColor: "#f9f9f9",
                    marginLeft: "0%"
                }}
            ></View>
        )
    }
    handleLoadMore = () => {
        if (!this.onEndReachedCalledDuringMomentum) {
            if (this.props.currentPage !== this.props.nextPage) {

                if (this.state.type === 'month') {
                    this.props.getMonthlyAppointment(this.props.currentPage + 1, false, this.state.seletedDate);
                } else {
                    this.props.getTodayAppointment(this.props.currentPage + 1, false, this.state.seletedDate);
                }
            }
            this.onEndReachedCalledDuringMomentum = true;
        }
    }

    _renderArrow = (direction) => {
        if (direction === 'left') {
            return <Image source={Images.back_arrow_white} style={styles.calArrows} />
        } else {
            return <Image source={Images.forword_arrow} style={styles.calArrows} />
        }
    }

    getDateString(timestamp) {
        
        const date = new Date(timestamp)
        const year = date.getFullYear();
        const month = date.getMonth() + 1;
        const day = date.getDate();
        
        let dateString = `${year}-`
        if (month < 10) {
            dateString += `0${month}-`
        } else {
            dateString += `${month}-`
        }
        if (day < 10) {
            dateString += `0${day}`
        } else {
            dateString += day
        }
        
        
        const timeUtc = moment.utc(timestamp).format('YYYY-MM-DD')
        return timeUtc
    }
    render() {
        let markedDateObject = {};
        let period = {};

        this.props.monthlyAppointmentList.map((value) => {

            const startDate = new Date(dateFormate(value.appointment_date.split(' ')[0]))
            const endDate = new Date(dateFormate(value.appointment_end_date.split(' ')[0]))


            let currentTimestamp = startDate.getTime();

            while (currentTimestamp < endDate.getTime()) {
                const dateString = this.getDateString(currentTimestamp)
                period[dateString] = {
                    // selected: true,
                    color: 'yellow',
                    startingDay: currentTimestamp === startDate.getTime()
                }
                currentTimestamp += 24 * 60 * 60 * 1000
            }
            const dateString = this.getDateString(endDate)
            period[dateString] = {
                // selected: true,
                color: 'yellow',
                endingDay: true,
            }
            period = {
                ...period
            }

            markedDateObject = {
                ...markedDateObject,
                [dateFormate(value.appointment_date.split(' ')[0])]: {
                    selected: true,
                    startingDay: true,
                    color: colors.Yellow,
                },
                [dateFormate(value.appointment_end_date.split(' ')[0])]: {
                    selected: true,
                    endingDay: true,
                    color: colors.Yellow,
                },

            };
        });

        return (
            <Container>
                <Content style={{ padding: 10 }}>
                    <Card>
                        <CardItem>
                            <Calendar
                                style={{ width: '100%' }}
                                onMonthChange={(month) => {
                                    this.onChangeMonth(month.month + '-' + month.year);
                                }}
                                onDayPress={(day) => {
                                    this.onPressDay(day.month + "-" + day.day + "-" + day.year);
                                }}

                                hideArrows={false}
                                renderArrow={this._renderArrow}
                                onPressArrowLeft={substractMonth => substractMonth()}
                                onPressArrowRight={addMonth => addMonth()}

                                hideExtraDays={false}
                                firstDay={1}
                                showWeekNumbers={true}

                                markedDates={
                                    period
                                }
                                markingType={'period'}

                            />
                        </CardItem>
                    </Card>
                    <View style={{ justifyContent: 'center', alignItems: 'center' }}>
                        {
                            this.state.seletedDate.length > 7 && <Text>Selected Date: {this.state.seletedDate}</Text>

                        }
                    </View>
                    <FlatList
                        style={{ height: '100%' }}
                        onRefresh={() =>
                            this.state.type === 'month' ?
                                this.props.getMonthlyAppointment(1, true, this.state.seletedDate) :

                                this.props.getTodayAppointment(1, true, this.state.seletedDate)
                        }
                        refreshing={this.props.refreshing}

                        // onMomentumScrollBegin={() => { this.onEndReachedCalledDuringMomentum = false; }}
                        // onEndReached={this.handleLoadMore}
                        // onEndReachedThreshold={0.5}
                        ListEmptyComponent={this.ListEmptyView}
                        // data={
                        //     this.props.todayAppointmentList.length === 0 ?
                        //         this.props.monthlyAppointmentList :
                        //         this.props.todayAppointmentList
                        // }
                        data={this.props.todayAppointmentList}
                        extraData={this.props}
                        ItemSeparatorComponent={this.renderSeperator}
                        renderItem={({ item }) => (
                            <ListItem onPress={() => this.OnItemPressed(item)}>
                                <Body >
                                    <Text>{item.title}</Text>
                                    <Text note>{item.address === null ? item.appointment_result : item.address}</Text>
                                    <Text note>{item.appointment_date}</Text>
                                </Body>
                                <Right>
                                    {/* <View style={{ flexDirection: 'row' }}> */}
                                    {/* <Text note>{item.appointment_date}</Text> */}
                                    <Image source={Images.forword_arrow_blue} style={styles.iconSize} />
                                    {/* </View> */}
                                </Right>
                            </ListItem>
                        )}
                        keyExtractor={(item, index) => item.id + "-" + index}
                    />
                </Content>
                {this.props.loading && <SpinnerView />}
            </Container >
        );
    }
}
const mapStateToPrps = (state) => {
    return {
        myAppointmentList: state.myAppointment.myAppointmentList,
        monthlyAppointmentList: state.myAppointment.monthlyAppointmentList,
        todayAppointmentList: state.myAppointment.todayAppointmentList,

        error: state.myAppointment.error,
        refreshing: state.myAppointment.refreshing,
        loading: state.myAppointment.loading,
        nextPage: state.myAppointment.nextPage,
        currentPage: state.myAppointment.currentPage
    }
}
export default connect(mapStateToPrps, {
    getMonthlyAppointment,
    getTodayAppointment
})(Calenders);