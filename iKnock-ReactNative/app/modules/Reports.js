import React from 'react';
import { View, Image, TouchableOpacity, FlatList } from 'react-native';
import { Grid, StackedBarChart, XAxis, YAxis, BarChart } from 'react-native-svg-charts';
import { Container, Content, Card, CardItem, ListItem, Left, Body, Right, List, Button } from 'native-base';
import { GraphIndicator } from '../reuseableComponents';
import Colors from '../assets/colors';
import PopupDialog, { SlideAnimation, DialogTitle } from 'react-native-popup-dialog';
import Images from "../assets";
import styles from "../assets/styles";
import { RadioGroup, Dropdowns, TextView } from "../reuseableComponents";
import { getReportData, getStatusList, getTenantUserList, getTypeList } from "../actions";
import { connect } from "react-redux";
import { SpinnerView } from '../Utility/common/SpinnerView';
import { Toasts } from "../Utility/showToast";
import { Text } from 'react-native-svg';

const keys = [];
const colors = [];

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
var lead_types = [
    { name: 'Percentage', id: 'percentage' },
    { name: 'Dollar', id: 'amount' },
];

class Reports extends React.PureComponent {
    state = {
        selectedValue: undefined,
        selectedTimePeriod: undefined,
        selectedStatus: undefined,
        selectedTypes: '',
        isChecked: true
    }
    static navigationOptions = ({ navigation }) => {
        const { params = {} } = navigation.state;
        return {
            headerRight: (
                <TouchableOpacity
                    onPress={() => params.handleThis()}
                >
                    <Image style={styles.iconRight} source={Images.ic_filter} />
                </TouchableOpacity>
            ),
        }
    };
    
    componentDidMount() {
        this.props.navigation.setParams({ handleThis: this.onPressFilterButton });
        //on back refresh
        this.didFocusListener = this.props.navigation.addListener(
            'didFocus',
            () => {
                // console.log('didFocus')
                this.props.getReportData(true, '', 'year');
            },
        );

         //call api
         this.props.getStatusList(false);
         this.props.getTenantUserList(1, true);
         //type list
         this.props.getTypeList(false);
    }
    componentDidUpdate() {
        if (this.props.error !== '') {
            Toasts.showToast(this.props.error)
        }
    }
    onPressFilterButton = () => {
        this.popupDialog.show();
    }
    onPressTenantCancelButton = () => {
        this.popupDialog.dismiss();
    }
    onValueChange(value) {
        this.setState({
            selectedValue: value,
        });
    }
    onChangeType = (id) => {
        this.setState({
            selectedTypes: id,
        });
    }
    onChangeTimePeriod = (id) => {
        this.setState({
            selectedTimePeriod: id,
        });
    }
    onChangeStatus = (id) => {
        this.setState({
            selectedStatus: id,
        });
    }
    onPrssApplyFilterButton = () => {
        this.popupDialog.dismiss();
        this.props.getReportData(true, this.state.selectedValue === -1 ? '' : this.state.selectedValue, this.state.selectedTimePeriod === -1 ? '' : this.state.selectedTimePeriod, this.state.selectedStatus === -1 ? '' : this.state.selectedStatus);
    }
    _renderChart = (report_data) => {
        const datas = [0]
        const axesSvg = { fontSize: 10, fill: 'grey' };
        const verticalContentInset = { top: 10, bottom: 10 }
        const xAxisHeight = 30;
        let maxValue = 0;
        let data = [];
        colors = [];
        keys = [];
        let total_value = 0;

        report_data.map((value) => {
            keys.push(value.label)
            colors.push(value[Object.keys(value).slice(1)].color_code)

            data.push({
                value: value[Object.keys(value).slice(1)].value,
                svg: {
                    fill: value[Object.keys(value).slice(1)].color_code,
                },
            });
            total_value = total_value + value[Object.keys(value).slice(1)].value; //total value

            if (value[Object.keys(value).slice(1)].value > maxValue) {
                maxValue = value[Object.keys(value).slice(1)].value
            }
        });

        datas.push(maxValue);
        const fill = 'rgb(134, 65, 244)';

        const CUT_OFF = 50
        const Labels = ({ x, y, bandwidth, data }) => (
            data.map((ee, index) => (
                <Text
                    key={index}
                    x={x(index) + (bandwidth / 2)}
                    y={ee.value < CUT_OFF ? y(ee.value) + 17 : y(ee.value) + 15}
                    fontSize={14}
                    fill={ee.value >= CUT_OFF ? 'black' : 'black'}
                    alignmentBaseline={'middle'}
                    textAnchor={'middle'}
                >
                    {ee.value}
                </Text>
            ))
        );
        return (

            <View>
                <TextView
                    title={"Filter"}
                    color={Colors.Black}
                    fontSize={20}
                    textAlign={'center'}
                    title={'Total: ' + total_value}
                />
                <View style={{ padding: 5 }}>
                    <Card>
                        <View style={{ height: 500, padding: 5, flexDirection: 'row' }}>
                            <YAxis
                                data={datas}
                                style={{ marginBottom: xAxisHeight }}
                                contentInset={verticalContentInset}
                                svg={axesSvg}
                            />
                            <View style={{ flex: 1, marginLeft: 10 }}>
                                <BarChart
                                    style={{ flex: 1 }}
                                    data={data}
                                    yAccessor={({ item }) => item.value}
                                    svg={{
                                        fill
                                    }}
                                    contentInset={{ top: 10, bottom: 10 }}
                                    spacing={0.2}
                                    gridMin={0}
                                >
                                    <Grid direction={Grid.Direction.HORIZONTAL} />
                                    <Labels />
                                </BarChart>
                                <XAxis
                                    style={{ marginHorizontal: -10, height: xAxisHeight }}
                                    data={report_data}
                                    formatLabel={(value, index) => report_data[index].label}
                                    contentInset={{ left: 25, right: 25 }}
                                    svg={axesSvg}
                                />
                            </View>
                        </View>
                    </Card>
                </View>
                <FlatList
                    scrollEnabled={false}
                    data={this.props.stateList}
                    extraData={this.props.stateList}
                    renderItem={({ item }) => (
                        <View style={{ flex: 1, flexDirection: 'column' }}>
                            <ListItem noBorder>
                                <Left>
                                    <GraphIndicator
                                        backgroundColor={item.color_code}
                                        title={item.title}
                                    />
                                </Left>
                            </ListItem>
                        </View>
                    )}
                    numColumns={2}
                    keyExtractor={(item, index) => item.id + "-" + index}
                />
            </View >
        );
    }
    render() {
        return (
            <Container>
                <Content>
                    {this.props.reportData.length > 0 ?

                        this._renderChart(this.props.reportData)
                        :
                        <View style={{ flex: 1, flexDirection: 'column', justifyContent: 'center', alignItems: 'center' }}>
                            <TextView
                                title={this.props.is_loading === true ? 'Loading...' : 'No Record Found'}
                                color={colors.Black}
                            />
                        </View>
                    }
                </Content>
                {/* filter dialog */}
                <PopupDialog dialogStyle={{ width: '100%', height: '100%' }}
                    ref={(popupDialog) => { this.popupDialog = popupDialog; }}
                    dialogAnimation={slideAnimation}>
                    <Container>
                        <View style={{ backgroundColor: Colors.DarkBlue, }}>
                            <ListItem icon noBorder>
                                <Left>
                                    <Button transparent onPress={() => this.onPressTenantCancelButton()}>
                                        <Image style={styles.toggleSize} source={Images.back_arrow_white} />
                                    </Button>
                                </Left>
                                <Body>
                                    <TextView
                                        title={"Filter"}
                                        color={Colors.White}
                                        fontSize={20}
                                        textAlign={'center'}
                                    />
                                </Body>
                                <Right>
                                    <Button transparent onPress={() => this.onPrssApplyFilterButton()}>
                                        <TextView
                                            title={"Apply"}
                                            color={Colors.White}
                                        />
                                    </Button>
                                </Right>
                            </ListItem>
                        </View>
                        <Content>
                            <List>
                                <ListItem noBorder>
                                    <TextView
                                        title={"Search By"}
                                        fontWeight={'bold'}
                                    />
                                </ListItem>
                                <ListItem noBorder>
                                    <Dropdowns
                                        listItems={this.props.tenantUserList}
                                        selectedValue={this.state.selectedValue}
                                        onValueChange={this.onValueChange.bind(this)}
                                        label={'All Users'}
                                        width={350}
                                        placeholder={'Select User'}
                                    />
                                </ListItem>
                                <ListItem noBorder>
                                    <TextView
                                        title={"Type"}
                                        fontWeight={'bold'}
                                    />
                                </ListItem>
                                <ListItem noBorder>
                                    <Dropdowns
                                        listItems={this.props.leadTypeList}
                                        selectedValue={this.state.selectedTypes}
                                        onValueChange={this.onChangeType.bind(this)}
                                        label={'All Type'}
                                        width={350}
                                        placeholder={'Select Type'}
                                    />
                                </ListItem>
                                <ListItem noBorder>
                                    <TextView
                                        title={"Time Period"}
                                        fontWeight={'bold'}
                                    />
                                </ListItem>
                                <ListItem noBorder>
                                    <Dropdowns
                                        listItems={radio_time_period}
                                        selectedValue={this.state.selectedTimePeriod}
                                        onValueChange={this.onChangeTimePeriod.bind(this)}
                                        label={'Select Time Period'}
                                        width={350}
                                        placeholder={'Select Time Period'}
                                    />
                                    {/* <RadioGroup
                                        data_props={radio_time_period}
                                        onPress={(id) => { this.onChangeTimePeriod(id) }}
                                    /> */}
                                </ListItem>
                                <ListItem noBorder>
                                    <TextView
                                        title={"Status"}
                                        fontWeight={'bold'}
                                    />
                                </ListItem>
                                <ListItem noBorder>
                                    {/* <RadioGroup
                                        data_props={this.props.stateList}
                                        onPress={(id) => { this.onChangeStatus(id) }}
                                    /> */}
                                    <Dropdowns
                                        listItems={this.props.stateList}
                                        selectedValue={this.state.selectedStatus}
                                        onValueChange={this.onChangeStatus.bind(this)}
                                        label={'All Status'}
                                        width={350}
                                        placeholder={'Select Status'}
                                    />
                                </ListItem>
                            </List>
                        </Content>
                    </Container>
                </PopupDialog>
                {this.props.is_loading && <SpinnerView />}
            </Container>
        )
    }
}
const slideAnimation = new SlideAnimation({
    slideFrom: 'bottom',
});

const mapStateToProps = (state) => {
    return {
        reportData: state.report.reportData,
        error: state.report.error,
        message: state.report.message,
        is_loading: state.report.is_loading,
        stateList: state.summery.stateList,
        tenantUserList: state.summery.tenantUserList,
        leadTypeList: state.summery.leadTypeList
    }
}
export default connect(mapStateToProps, {
    getReportData,
    getStatusList,
    getTenantUserList,
    getTypeList
})(Reports);