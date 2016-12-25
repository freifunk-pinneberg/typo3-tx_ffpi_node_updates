
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

plugin.tx_ffpinodeupdates._CSS_DEFAULT_STYLE (
    textarea.f3-form-error {
        background-color:#FF9F9F;
        border: 1px #FF0000 solid;
    }

    input.f3-form-error {
        background-color:#FF9F9F;
        border: 1px #FF0000 solid;
    }

    .tx-ffpi-node-updates table {
        border-collapse:separate;
        border-spacing:10px;
    }

    .tx-ffpi-node-updates table th {
        font-weight:bold;
    }

    .tx-ffpi-node-updates table td {
        vertical-align:top;
    }

    .typo3-messages .message-error {
        color:red;
    }

    .typo3-messages .message-ok {
        color:green;
    }
)
