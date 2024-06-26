import React, { Component } from "react";
import { FlatList, View, Text } from "react-native";
import ListEmpty from "./ListEmpty";
import ListFooter from "./ListFooter";

class FlatListHandler extends Component {

  keyExtractor = (item, index) => `item_${index}`;

  onEndReached = () => {
    this.props.fetchRequest &&
      this.props.data.length % 25 === 0 &&
      this.props.fetchRequest(this.props.data.length / 25 + 1, false);
  };

  onRefresh = () =>
    this.props.fetchRequest && this.props.fetchRequest(1, true);

  renderItem = ({ index }) => (
    <View>
      <Text>{`item ${index}`}</Text>
    </View>
  );

  renderListEmpty = () => (!this.props.data.length ?
    <ListEmpty
      isRefreshing={this.props.isFetching}
    /> : null);

  renderListFooter = () => {
    return !this.props.isFetching && this.props.data.length % 25 === 0 && this.props.data.length !== 0 ? (
      <ListFooter />
    ) : null;
  };

  render() {

    /* Rendering contains all the basic stuff list needs to render it self what ever extra props is passed to is overridden */
    return (
      <FlatList
        data={this.props.data}
        renderItem={this.renderItem}
        refreshing={this.props.isFetching}
        onRefresh={this.onRefresh}
        onEndReached={this.onEndReached}
        keyExtractor={this.keyExtractor}
        onEndReachedThreshold={0.5}

        ListEmptyComponent={this.renderListEmpty}
        ListFooterComponent={this.renderListFooter}
        contentContainerStyle={
          this.props.data.length ? {} : styles.contentContainerStyle
        }
        {...this.props}
      />
    );
  }
}

const styles = {
  contentContainerStyle: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center"
  }
};

export default FlatListHandler;
