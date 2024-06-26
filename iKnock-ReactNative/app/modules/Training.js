import React, { Component } from 'react';
import { FlatList, Image, View } from 'react-native';
import { Container, ListItem, Left, Text, Right } from 'native-base';
import { SearchFieldWithoutIcon } from '../reuseableComponents';
import Images from '../assets';
import styles from '../assets/styles';
import { getTrainerList } from "../actions";
import { connect } from "react-redux";

class Training extends Component {
    state = {
        isSearching: "",
    }
    componentDidMount() {
        this.flatListHandlerFetchData();
    }
    flatListHandlerFetchData = (page = 1, isConcat = true) => {
        const { isSearching } = this.state;

        this.props.getTrainerList(page, isConcat, isSearching)
    }

    onItemPressed = (item) => {
        this.props.navigation.navigate('ownerIntroduction',
            {
                'trainer_detail': item
            });
    }
    handleLoadMore = () => {
        if (!this.onEndReachedCalledDuringMomentum) {
            if (this.props.currentPage !== this.props.nextPage) {
                this.flatListHandlerFetchData(this.props.currentPage + 1, false)
            }
            this.onEndReachedCalledDuringMomentum = true;
        }
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

    render() {
        const { isSearching } = this.state;
        return (
            <Container>
                <View>
                    <SearchFieldWithoutIcon
                        onChangeText={(text) =>
                            this.setState({ isSearching: text }, this.flatListHandlerFetchData)
                        }
                        value={isSearching}
                    />

                    <FlatList
                        refreshing={this.props.refreshing}
                        onRefresh={() => this.setState({ isSearching: '' }, this.flatListHandlerFetchData)}
                        onMomentumScrollBegin={() => this.onEndReachedCalledDuringMomentum = false}
                        onEndReached={this.handleLoadMore}
                        onEndReachedThreshold={0.5}
                        style={{ height: '100%' }}
                        data={this.props.trainerList}
                        extraData={this.props.trainerList}
                        ListEmptyComponent={this.ListEmptyView}
                        ItemSeparatorComponent={this.renderSeperator}
                        renderItem={({ item }) => (

                            <ListItem style={{ height: 100 }}
                                onPress={() => this.onItemPressed(item)} noBorder>
                                <Left>
                                    <Text>{item.title}</Text>
                                </Left>
                                <Right>
                                    <Image source={Images.forword_arrow_blue} style={styles.iconSize} />
                                </Right>
                            </ListItem>
                        )}
                        keyExtractor={(item, index) => item.id + '-' + index}
                    />
                </View>
            </Container>
        )
    }
}

const mapStatToProps = (state) => {
    return {
        trainerList: state.trainers.trainerList,
        error: state.trainers.error,
        refreshing: state.trainers.refreshing,
        loading: state.trainers.loading,
        nextPage: state.trainers.nextPage,
        currentPage: state.trainers.currentPage
    }
}

export default connect(mapStatToProps, {
    getTrainerList
})(Training);