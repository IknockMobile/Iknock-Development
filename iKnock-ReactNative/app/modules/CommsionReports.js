import React from 'react';
import { View, Image, TouchableOpacity } from "react-native";
import { PieChart } from 'react-native-svg-charts';
import { Container, Content, ListItem, Left, Body, Right, Text, List, Button } from 'native-base';
import { GraphIndicator } from '../reuseableComponents'
import * as Progress from 'react-native-progress';
import Colors from '../assets/colors';
import PopupDialog, { SlideAnimation } from 'react-native-popup-dialog';
import Images from "../assets";
import styles from "../assets/styles";
import colors from "../assets/colors";
import { Dropdowns, MultiSelectValues } from "../reuseableComponents";
import { getCommissionReportData, getTenantUserList, getTypeList } from "../actions";
import { connect } from "react-redux";
import { FlatList } from 'react-native-gesture-handler';
import { SpinnerView } from '../Utility/common/SpinnerView';
import { Toasts } from "../Utility/showToast";
import { removeSquareBrackets } from "../Utility";

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

class CommsionReports extends React.PureComponent {

    constructor(props) {
        super(props);
        this.setRef = this.setRef.bind(this);
        this.state = {
            selectedUser: [],
            selectedTimePeriod: '',
            selectedValue: 'percentage',
            selectName: 'All Users',
            selectedTypes: [],
            filterCount: 0
        };
    }
    static navigationOptions = ({ navigation }) => {
        const { params = {} } = navigation.state;
        return {
            headerRight: (
                <TouchableOpacity
                    onPress={() => params.handleThis()}>
                    <Image style={styles.iconRight} source={Images.ic_filter} />
                </TouchableOpacity>
            ),
        }
    };
  
    componentDidMount() {
        this.props.navigation.setParams({ handleThis: this.onPressFilterButton });
        this.flatListHandlerFetchData();

        //user list
        this.props.getTenantUserList(1, true);
        //type list
        this.props.getTypeList(false);
    }
    flatListHandlerFetchData = (page = 1, isConcat = true) => {
        const { selectedTimePeriod, selectedValue, selectedTypes, selectedUser } = this.state;

        let timePeriod = selectedTimePeriod === -1 ? '' : selectedTimePeriod;
        let amount = selectedValue === -1 ? '' : selectedValue;
        let userIds = removeSquareBrackets(selectedUser);
        let selectedTypesId = removeSquareBrackets(selectedTypes);

        this.props.getCommissionReportData(page, isConcat, userIds, timePeriod, amount,
            selectedTypesId, this.cbSuccess, this.cbFailer);

    }
    cbSuccess = (respponse) => {
        //success
    }
    cbFailer = (error) => {
        Toasts.showToast(error)
    }

    setRef(ref) {
        this.popupDialog = ref;
    }
    onPressFilterButton = () => {
        this.popupDialog.show();
    }
    onChangeUser(ids) {
        this.setState({
            selectedUser: ids,
        });
    }
    onChangeTimePeriod = (id) => {
        this.setState({
            selectedTimePeriod: id,
        });
    }
    onChangeValue = (id) => {
        this.setState({
            selectedValue: id,
        });
    }
    onChangeType = (id) => {
        this.setState({
            selectedTypes: id,
        });
    }
    onPrssApplyFilterButton = () => {
        this.flatListHandlerFetchData();
        this.popupDialog.dismiss();
    }
    onPrssClearAllFilter = () => {
        this.setState({
            selectedTimePeriod: '',
            selectedUser: [],
            selectedTypes: [],
            selectedValue: '',
            filterCount: 0
        }, this.flatListHandlerFetchData);
    }
    onPressCancelButton = () => {
        this.popupDialog.dismiss();
    }
    _renderChart = (data) => {

        const { selectedValue } = this.state;
        // this.props.tenantUserList.map((s) => {
        //     if (s.id === this.state.selectedUser) {
        //         this.setState({ selectName: s.name });
        //     }
        //     if (this.state.selectedUser === -1) {
        //         this.setState({ selectName: 'All Users' });
        //     }
        // });
        return (
            <View>
                {/* <ListItem noBorder style={{ height: 60 }}>
                    <Left>
                        <Text style={{ marginStart: 10 }}>{this.state.selectName}</Text>
                    </Left>
                </ListItem> */}
                <PieChart
                    style={{ height: 400 }}
                    outerRadius={'70%'}
                    innerRadius={10}
                    data={data}
                />
                <FlatList
                    scrollEnabled={false}
                    data={data}
                    extraData={data}
                    renderItem={({ item }) => (
                        <ListItem noBorder>
                            <Left>
                                <GraphIndicator
                                    backgroundColor={item.svg.fill}
                                    title={item.title}
                                />
                            </Left>
                            <Body><Progress.Bar progress={
                                selectedValue === 'amount' ?
                                    item.value
                                    : (item.value / 100)
                            } width={130} color={item.svg.fill} /></Body>
                            <Right><Text note> {item.value} / {item.total_commission}</Text></Right>
                        </ListItem>
                    )}
                    keyExtractor={(item, index) => item.key + "-" + index}
                />
            </View>
        );
    }
    render() {
        const { selectedTimePeriod, selectedValue, selectedTypes,
            selectedUser } = this.state;

        let appliedFiltersCount = 0;

        if (selectedValue.length) {
            appliedFiltersCount++;
        }
        if (selectedTypes.length) {
            appliedFiltersCount++;
        }
        if (selectedUser.length) {
            appliedFiltersCount++;
        }
        if (selectedTimePeriod.length) {
            appliedFiltersCount++;
        }

        return (
            <Container style={styles.container}>
                {
                    appliedFiltersCount !== 0 &&
                    <Text
                        style={{
                            fontWeight: 'bold',
                            fontSize: 16,
                            textAlign: 'center',
                            color: Colors.Black
                        }}
                    >{"Applied Filter: " + appliedFiltersCount}</Text>
                }
                <Content>
                    {this.props.commissionData.length > 0 ?
                        this._renderChart(this.props.commissionData)
                        :
                        <View style={{ flex: 1, flexDirection: 'column', justifyContent: 'center', alignItems: 'center' }}>
                            <Text style={{ color: colors.Black }}>
                                {
                                    this.props.is_loading === true ? 'Loading...' : 'No Record Found'
                                }
                            </Text>
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
                                    <Button transparent onPress={() => this.onPressCancelButton()}>
                                        <Image style={styles.toggleSize} source={Images.back_arrow_white} />
                                    </Button>
                                </Left>
                                <Body>
                                    <Text style={{ color: Colors.White, fontSize: 20, textAlign: 'center' }}>Filter</Text>
                                </Body>
                                <Right>
                                    <Button transparent
                                        onPress={() => this.onPrssClearAllFilter()}
                                    >
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
                                <ListItem noBorder>
                                    <MultiSelectValues
                                        listItems={this.props.tenantUserList}
                                        selectedItems={this.state.selectedUser}
                                        onSelectedItemsChange={this.onChangeUser.bind(this)}
                                        InputPlaceholder={'Search User'}
                                        type={false}
                                    />
                                </ListItem>
                                <ListItem noBorder>
                                    <Text style={{ fontWeight: 'bold' }}>Type</Text>
                                </ListItem>
                                <ListItem noBorder>

                                    <MultiSelectValues
                                        listItems={this.props.leadTypeList}
                                        selectedItems={this.state.selectedTypes}
                                        onSelectedItemsChange={this.onChangeType.bind(this)}
                                        InputPlaceholder={'Search Lead Type'}
                                        type={true}
                                    />

                                </ListItem>
                                <ListItem noBorder>
                                    <Text style={{ fontWeight: 'bold' }}>Time Period</Text>
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

                                </ListItem>
                                <ListItem noBorder>
                                    <Text style={{ fontWeight: 'bold' }}>Show Amount</Text>
                                </ListItem>
                                <ListItem noBorder>
                                    <Dropdowns
                                        listItems={radio_show_amount}
                                        selectedValue={this.state.selectedValue}
                                        onValueChange={this.onChangeValue.bind(this)}
                                        label={'Select Amount'}
                                        width={350}
                                        placeholder={'Select Amount'}
                                    />

                                </ListItem>
                            </List>
                        </Content>
                        <Button block style={{
                            margin: 5,
                            minHeight: 50,
                            backgroundColor: colors.btnDarkBlueBgColor
                        }}
                            onPress={() => this.onPrssApplyFilterButton()} >
                            <Text style={{ color: colors.White }} uppercase={false}> {'Apply Filters'} </Text>

                        </Button>
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
        commissionData: state.report.commissionData,
        error: state.report.error,
        message: state.report.message,
        is_loading: state.report.is_loading,
        tenantUserList: state.summery.tenantUserList,
        leadTypeList: state.summery.leadTypeList
    }
}
export default connect(mapStateToProps, {
    getCommissionReportData,
    getTenantUserList,
    getTypeList
})(CommsionReports);