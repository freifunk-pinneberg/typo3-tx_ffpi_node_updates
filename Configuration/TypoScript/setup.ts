
plugin.tx_ffpinodeupdates_nodeabo {
  view {
    templateRootPaths.0 = EXT:ffpi_node_updates/Resources/Private/Templates/
    templateRootPaths.1 = {$plugin.tx_ffpinodeupdates_nodeabo.view.templateRootPath}
    partialRootPaths.0 = EXT:ffpi_node_updates/Resources/Private/Partials/
    partialRootPaths.1 = {$plugin.tx_ffpinodeupdates_nodeabo.view.partialRootPath}
    layoutRootPaths.0 = EXT:ffpi_node_updates/Resources/Private/Layouts/
    layoutRootPaths.1 = {$plugin.tx_ffpinodeupdates_nodeabo.view.layoutRootPath}
  }
  persistence {
    storagePid = {$plugin.tx_ffpinodeupdates_nodeabo.persistence.storagePid}
    #recursive = 1
  }
  features {
    #skipDefaultArguments = 1
  }
  mvc {
    #callDefaultActionIfActionCantBeResolved = 1
  }
}
