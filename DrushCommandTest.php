<?php

namespace Drupal\drush_test\Commands;

use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 */
class DrushCommandTest extends DrushCommands {
/**
   * Echos back hello with the argument provided.
   *
   * @param string $name
   *   Argument provided to the drush command.
   *
   * @command drush_test:hello
   * @aliases d-hello
   * @options array that takes multiple values.
   * @options msg Whether or not an extra message should be displayed to the user.
   * @usage drush_test:hello akanksha --msg
   *   Display 'Hello Akanksha!' and a message.
   */
  public function hello($name, $options = ['msg' => FALSE]) {
    if ($options['msg']) {
      $this->output()->writeln('Hello ' . $name . '! Finally! A working Drush 9 command.');
    }
    else {
      $this->output()->writeln('Hello ' . $name . '!');
    }
  }

  ////////////***************************************************////////////

  /**
   * Read a CSV file and display data in terminal
   * 
   * @param $filename Name of file that is being read
   *    Argument provided to the drush command
   * 
   * @command drush_test:readCSV
   * @aliases d-readCSV
   * @usage drush_test:readCSV /filepath/example.csv
   *    Display contents of example.csv
   */
  function readCSV($filename) {

    // Check to see that the file exists
    if (!file_exists($filename)) {
      die('The file ' . $filename . ' does not exist');
    } else {
      $this->output()->writeln('The file ' . $filename . ' exists');
    }

    $data = [];
    
    // Open the file
    $f = fopen($filename, 'r');

    if ($f) {
      // process the file
      $this->output()->writeln('The file ' . $filename . ' is open');  

      // Read each line CSV file at a time and place into array
      while (($row = fgetcsv($f, 0, ',')) !== false) {
        $data[] = $row;
      }

      // Display array
      print_r($data);

      // Close the file
      if (fclose($f)) {
          $this->output()->writeln('The file ' . $filename . ' is closed');
      }

    } else {
      // Throw error message if file is not opened
      die('Cannot open the file ' . $filename);
    }
  }
  
  ////////////***************************************************////////////

  /**
   * Read a CSV file and import data to a fast_food_chain content type.
   * 
   * @param $filename Name of file that is being read
   *    Argument provided to the drush command
   * 
   * @command drush_test:importCSVcreateFFC
   * @aliases d-importCSVcreateFFC
   * @usage drush_test:importCSVcreateFFC /filepath/example.csv
   *    Creates fast_food_chain content type nodes using data from example.csv
   */
  function importCSVcreateFFC($filename) {
  
    // Check to see that the file exists
    if (!file_exists($filename)) {
      die('The file ' . $filename . ' does not exist');
    } else {
      $this->output()->writeln('The file ' . $filename . ' exists');
    }
  
    // Declare array to store CSV data
    $data = [];
      
    // Open the file
    $f = fopen($filename, 'r');
  
    if ($f) {
      // process the file
      $this->output()->writeln('The file ' . $filename . ' is open');  
  
      // Read each line from CSV file at a time and place each into an array
      while (($row = fgetcsv($f, 0, ',')) !== false) {
        $data[] = $row;
      }

      $i = 0; // Variable for counting items processed
      $new_nodes_created = 0; // Variable for counting nodes created
      $nodes_updated = 0; // Variable for counting nodes update
  
      // Create node storage
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
  
      // Loop thorugh CSV data
      foreach($data as $record) {
  
        // $this->output()->writeln('Importing Chain with ID: ' . $record[1] . ' - ' . $record[0]);        

        // For each row in CSV call the createProduct function
        $this->createOrUpdateChain($record, $new_nodes_created, $nodes_updated, $node_storage);

        $i++;
      }
  
      $this->output()->writeln('Total items processed: ' . $i);
      $this->output()->writeln('Total new food-chain entries created: ' . $new_nodes_created);
      $this->output()->writeln('Total food-chain entries updated: ' . $nodes_updated);

      // Close the file
      if (fclose($f)) {
          $this->output()->writeln('The file ' . $filename . ' is closed');
      }
  
    } else {
      // Throw error message if file is not opened
      die('Cannot open the file ' . $filename);
    } 
  }
  
  /**
   * Function to create or update fast-food chain content type nodes from imported CSV data
   * @param $record Row from CSV
   * @param $new_nodes_created Counter
   * @param $nodes_updated Counter 
   * @param $node_storge Empty node
   */
  function createOrUpdateChain($record, &$new_nodes_created, &$nodes_updated, $node_storage) {

    // Search for exisitng node
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('field_fast_food_chain_id.value', $record[1], '=');
    $nids = $query->execute();
    if ($nid = reset($nids)) {
      $chain = $node_storage->load($nid);
    }

    // Set node title to fast-food chain name
    $title = $record[0];        

    // If node exists then update else create new node
    if ($chain) {
      $chain->title->value = $record[0]; 
      $chain->field_fast_food_chain_id->value = $record[1]; 

      $chain->save();
      $nodes_updated++;
    } else {
      if (!empty($title)) {
        // Create new fast-food chain node and save
        $chain = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->create([
            'type' => 'fast_food_chain',
            'title' => $title,
            'uid' => 1
          ]);

        $chain->field_fast_food_chain_id->value = $record[1]; 

        $chain->save();
        
        $new_nodes_created++;
      }
    }
  }

  ////////////***************************************************////////////

  /**
   * Read a CSV file and import data to a master product content type.
   * 
   * @param $filename Name of file that is being read
   *    Argument provided to the drush command
   * 
   * @command drush_test:importCSVcreateMP
   * @aliases d-importCSVcreateMP
   * @usage drust_test:importCSVcreateMP /filepath/example.csv
   *    Creates master_product content type nodes using data from example.csv
   */
  function importCSVcreateMP($filename) {
  
    // Check to see that the file exists
    if (!file_exists($filename)) {
      die('The file ' . $filename . ' does not exist');
    } else {
      $this->output()->writeln('The file ' . $filename . ' exists');
    }
  
    // Declare array to store CSV data
    $data = [];
      
    // Open the file
    $f = fopen($filename, 'r');
  
    if ($f) {
      // process the file
      $this->output()->writeln('The file ' . $filename . ' is open');  
  
      // Read each line from CSV file at a time and place each into an array
      while (($row = fgetcsv($f, 0, ',')) !== false) {
        $data[] = $row;
      }

      $i = 0; // Variable for counting items processed
      $new_nodes_created = 0; // Variable for counting nodes created
      $nodes_updated = 0; // Variable for counting nodes updated
      
  
      // Create node storage
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
  
      // Loop thorugh CSV data
      foreach($data as $record) {
  
        // $this->output()->writeln('Importing Master Product with ID: ' . $record[0] . ' - ' . $record[1]);        

        // For each row in CSV call the createOrUpdateMasterProduct function
        $this->createOrUpdateMasterProduct($record, $new_nodes_created, $nodes_updated, $node_storage);

        $i++;
      }
  
      $this->output()->writeln('Total items processed: ' . $i);
      $this->output()->writeln('Total new master-product entries created: ' . $new_nodes_created);
      $this->output()->writeln('Total master-product entries updated: ' . $nodes_updated);

      // Close the file
      if (fclose($f)) {
          $this->output()->writeln('The file ' . $filename . ' is closed');
      }
  
    } else {
      // Throw error message if file is not opened
      die('Cannot open the file ' . $filename);
    } 
  }

  /**
   * Function to create or update master product content type nodes from imported CSV data
   * @param $record Row from CSV
   * @param $new_nodes_created Counter
   * @param $nodes_updated Counter 
   * @param $node_storge Empty node
   */
  function createOrUpdateMasterProduct($record, &$new_nodes_created, &$nodes_updated, $node_storage) {

    // Search for exisitng node
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('field_master_product_id.value', $record[0], '=');

    $nids = $query->execute();

    if ($nid = reset($nids)) {
      $master_product = $node_storage->load($nid);
    }

    // Set node title to master product name
    $title = $record[1];        

    // If node exists then update...
    // This update functionality will handle repeat entries in CSV 
    // E.G. Same product entered multiple times (once a year over multiple years)
    // There will end up being one node per product.
    // Although the remaining node/product may not necessarily be the most recent CSV entry... 
    // by year this is not problematic as information stored in Master Product is unchanging.
    if ($master_product) {
      $master_product->title->value = $title; 
      $master_product->field_master_product_id->value = $record[0];
      $master_product->field_master_product_name->value = $record[1];
      
      // Process Food Group (entity reference)
      // First assign the item the appropriate food group
      if(!empty($record[6])) {
        $master_food_group_id = $record[6];
        $d_master_food_group_id = $this->getDrupalFoodGroupID($master_food_group_id);
        $master_product->field_master_food_group->target_id = $d_master_food_group_id;
      }

      // Check if item has a major category
      if (!empty($record[8])) {
        $master_major_category_id = $record[8];
        $maj_cat = $master_major_category_id[2] . $master_major_category_id[3];

        // If item has a maj cat then replace food group with it
        if($maj_cat !== '00') {
          $d_master_major_category_id = $this->getDrupalMajorCategoryID($master_major_category_id);  
          $master_product->field_master_food_group->target_id = $d_master_major_category_id;
        }
      } 

      // Check if item has a minor category
      if (!empty($record[10])) {
        $master_minor_category_id = $record[10];
        $min_cat = $master_minor_category_id[4] . $master_minor_category_id[5];

        // If item has a min cat then replace maj cat with it
        if($min_cat !== '00') {
          $d_master_minor_category_id = $this->getDrupalMinorCategoryID($master_minor_category_id);  
          $master_product->field_master_food_group->target_id = $d_master_minor_category_id;
        }
      }

      $master_product->save();

      $nodes_updated++;

    // If node does not exist then create new node
    } else {
      if (!empty($title)) {
        // Create new master product node and save
        $master_product = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->create([
            'type' => 'master_product',
            'title' => $title,
            'uid' => 1
          ]);

        $master_product->field_master_product_id->value = $record[0];
        $master_product->field_master_product_name->value = $record[1];

        // Process Fast-Food Chain (entity reference)
        if(!empty($record[4])) {
          $master_fast_food_chain_id = $record[4];
          $d_master_fast_food_chain_id = $this->getDrupalFastFoodChainID($master_fast_food_chain_id);
          $master_product->field_master_fast_food_chain->target_id = $d_master_fast_food_chain_id;
        }
        
        // Process Food Group (entity reference)
        // First assign the item the appropriate food group
        if(!empty($record[6])) {
          $master_food_group_id = $record[6];
          $d_master_food_group_id = $this->getDrupalFoodGroupID($master_food_group_id);
          $master_product->field_master_food_group->target_id = $d_master_food_group_id;
        }

        // Check if item has a major category
        if (!empty($record[8])) {
          $master_major_category_id = $record[8];
          $maj_cat = $master_major_category_id[2] . $master_major_category_id[3];

          // If item has a maj cat then replace food group with it
          if($maj_cat !== '00') {
            $d_master_major_category_id = $this->getDrupalMajorCategoryID($master_major_category_id);  
            $master_product->field_master_food_group->target_id = $d_master_major_category_id;
          }
        } 

        // Check if item has a minor category
        if (!empty($record[10])) {
          $master_minor_category_id = $record[10];
          $min_cat = $master_minor_category_id[4] . $master_minor_category_id[5];

          // If item has a min cat then replace maj cat with it
          if($min_cat !== '00') {
            $d_master_minor_category_id = $this->getDrupalMinorCategoryID($master_minor_category_id);  
            $master_product->field_master_food_group->target_id = $d_master_minor_category_id;
          }
        }

        $master_product->save();

        $new_nodes_created++;
      }
    }
  }

  ////////////***************************************************////////////

  /**
   * Read a CSV file and import data from a previous year to a product content type.
   * 
   * @param $filename Name of file that is being read
   *    Argument provided to the drush command
   * 
   * @command drush_test:importCSVcreatePREV
   * @aliases d-importCSVcreatePREV
   * @usage drush_test:importCSVcreatePREV /filepath/example.csv
   *    Creates fast_food_chain content type nodes using data from example.csv
   */
  function importCSVcreatePREV($filename) {
  
    // Check to see that the file exists
    if (!file_exists($filename)) {
      die('The file ' . $filename . ' does not exist');
    } else {
      $this->output()->writeln('The file ' . $filename . ' exists');
    }
  
    // Declare an array to store the CSV data
    $data = [];
      
    // Open the file
    $f = fopen($filename, 'r');
  
    if ($f) {
      // process the file
      $this->output()->writeln('The file ' . $filename . ' is open');  
  
      // Read each line from CSV file at a time and place each into an array
      while (($row = fgetcsv($f, 0, ',')) !== false) {
        $data[] = $row;
      }
  
      $i = 0; // Variable for counting items processed
      $new_nodes_created = 0; // Variable for counting nodes created
      $nodes_updated = 0; // Variable for counting nodes updated
      
      // Create node storage
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
  
      // Loop thorugh CSV data
      foreach($data as $record) {
  
        // $this->output()->writeln('Importing Previous Product with ID: ' . $record[0] . ' - ' . $record[1]);        

        // For each row in CSV call the createOrUpdateMasterProduct function
        $this->createOrUpdatePreviousYearProduct($record, $new_nodes_created, $nodes_updated, $node_storage);

        $i++;
      }
  
      $this->output()->writeln('Total items processed: ' . $i);
      $this->output()->writeln('Total new previous year products created: ' . $new_nodes_created);
      $this->output()->writeln('Total previous year products updated: ' . $nodes_updated);

      // Close the file
      if (fclose($f)) {
          $this->output()->writeln('The file ' . $filename . ' is closed');
      }
  
    } else {
      // Throw error message if file is not opened
      die('Cannot open the file ' . $filename);
    } 
  }

  /**
   * Function to create or update master product content type nodes from imported CSV data
   * @param $record Row from CSV
   * @param $new_nodes_created Counter
   * @param $nodes_updated Counter 
   * @param $node_storge Empty node
   */
  function createOrUpdatePreviousYearProduct($record, &$new_nodes_created, &$nodes_updated, $node_storage) {

    // Search for exisitng node
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('field_product_id.value', $record[0], '=')
      ->condition('field_year.value', $record[2], '=');
    
      $nids = $query->execute();

    if ($nid = reset($nids)) {
      $previous_year_product = $node_storage->load($nid);
    }

    // Set node title to master product name
    $title = $record[1];    

    // If node exists then update
    if($previous_year_product) {

      $previous_year_product->title->value = $title; 
      $previous_year_product->field_product_id->value = $record[0];
      $previous_year_product->field_product_name->value = $record[1];
      $previous_year_product->field_year->value = $record[2];

      // Process Fast-Food Chain (entity reference)
      if(!empty($record[4])) {
        $fast_food_chain_id = $record[4];
        $d_fast_food_chain_id = $this->getDrupalFastFoodChainID($fast_food_chain_id);
        $previous_year_product->field_fast_food_chain->target_id = $d_fast_food_chain_id;
      }

      // Process Food Group (entity reference)
      // First assign the item the appropriate food group
      if(!empty($record[6])) {
        $food_group_id = $record[6];
        $d_food_group_id = $this->getDrupalFoodGroupID($food_group_id);
        $previous_year_product->field_food_group->target_id = $d_food_group_id;
      }

      // Process Major Category (entity reference)
      // Check if item has a major category
      if (!empty($record[8])) {
        $major_category_id = $record[8];
        $maj_cat = $major_category_id[2] . $major_category_id[3];

        // If item has a maj cat then replace food group with it
        if($maj_cat !== '00') {
          $d_major_category_id = $this->getDrupalMajorCategoryID($major_category_id);  
          $previous_year_product->field_food_group->target_id = $d_major_category_id;
        }
      } 
      
      // Process Minor Category (entity reference)
      // Check if item has a minor category
      if (!empty($record[10])) {
        $minor_category_id = $record[10];
        $min_cat = $minor_category_id[4] . $minor_category_id[5];

        // If item has a min cat then replace maj cat with it
        if($min_cat !== '00') {
          $d_minor_category_id = $this->getDrupalMinorCategoryID($minor_category_id);  
          $previous_year_product->field_food_group->target_id = $d_minor_category_id;
        }
      }

      $previous_year_product->field_data_source->value = $record[11];
      $previous_year_product->field_healthier_option->value = $record[12];
      $previous_year_product->field_serving_unit->value = $record[13];
      $previous_year_product->field_serving_size->value = $record[14];
      $previous_year_product->field_package_unit->value = $record[15];
      $previous_year_product->field_package_size->value = $record[16];
      $previous_year_product->field_energy_kj_100g->value = $record[17];
      $previous_year_product->field_protein_g_100g->value = $record[18];
      $previous_year_product->field_total_fat_g_100g->value = $record[19];
      $previous_year_product->field_saturated_fat_g_100g->value = $record[20];
      $previous_year_product->field_carbohydrate_g_100g->value = $record[21];
      $previous_year_product->field_sugar_g_100g->value = $record[22];
      $previous_year_product->field_sodium_mg_100g->value = $record[23];
      $previous_year_product->field_fibre_g_100g->value = $record[24];
      $previous_year_product->field_trans_fat_g_100g->value = $record[25];
      $previous_year_product->field_poly_fat_g_100g->value = $record[26];
      $previous_year_product->field_mono_fat_g_100g->value = $record[27];
      $previous_year_product->field_energy_kj_serve->value = $record[28];
      $previous_year_product->field_protein_g_serve->value = $record[29];
      $previous_year_product->field_total_fat_g_serve->value = $record[30];
      $previous_year_product->field_saturated_fat_g_serve->value = $record[31];
      $previous_year_product->field_carbohydrate_g_serve->value = $record[32];
      $previous_year_product->field_sugar_g_serve->value = $record[33];
      $previous_year_product->field_sodium_mg_serve->value = $record[34];
      $previous_year_product->field_fibre_g_serve->value = $record[35];
      $previous_year_product->field_trans_fat_g_serve->value = $record[36];
      $previous_year_product->field_poly_fat_g_serve->value = $record[37];
      $previous_year_product->field_mono_fat_g_serve->value = $record[38];
      $previous_year_product->field_notes_on_missing_data->value = $record[39];
      $previous_year_product->field_additional_notes->value = $record[40];
      $previous_year_product->field_all_information->value = TRUE;

      $previous_year_product->save();

      $nodes_updated++;

    // If node does not exist then create new node
    } else {
      if (!empty($title)) {
        // Create new master product node and save
        $previous_year_product = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->create([
            'type' => 'product',
            'title' => $title,
            'uid' => 1
          ]);

        $previous_year_product->field_product_id->value = $record[0];
        $previous_year_product->field_product_name->value = $record[1];
        $previous_year_product->field_year->value = $record[2];

        // Process Fast-Food Chain (entity reference)
        if(!empty($record[4])) {
          $fast_food_chain_id = $record[4];
          $d_fast_food_chain_id = $this->getDrupalFastFoodChainID($fast_food_chain_id);
          $previous_year_product->field_fast_food_chain->target_id = $d_fast_food_chain_id;
        }

        // Process Fast-Food Chain (entity reference)
        if(!empty($record[4])) {
          $fast_food_chain_id = $record[4];
          $d_fast_food_chain_id = $this->getDrupalFastFoodChainID($fast_food_chain_id);
          $previous_year_product->field_fast_food_chain->target_id = $d_fast_food_chain_id;
        }

        // Process Food Group (entity reference)
        // First assign the item the appropriate food group
        if(!empty($record[6])) {
          $food_group_id = $record[6];
          $d_food_group_id = $this->getDrupalFoodGroupID($food_group_id);
          $previous_year_product->field_food_group->target_id = $d_food_group_id;
        }

        // Process Major Category (entity reference)
        // Check if item has a major category
        if (!empty($record[8])) {
          $major_category_id = $record[8];
          $maj_cat = $major_category_id[2] . $major_category_id[3];

          // If item has a maj cat then replace food group with it
          if($maj_cat !== '00') {
            $d_major_category_id = $this->getDrupalMajorCategoryID($major_category_id);  
            $previous_year_product->field_food_group->target_id = $d_major_category_id;
          }
        } 
      
        // Process Minor Category (entity reference)
        // Check if item has a minor category
        if (!empty($record[10])) {
          $minor_category_id = $record[10];
          $min_cat = $minor_category_id[4] . $minor_category_id[5];

          // If item has a min cat then replace maj cat with it
          if($min_cat !== '00') {
            $d_minor_category_id = $this->getDrupalMinorCategoryID($minor_category_id);  
            $previous_year_product->field_food_group->target_id = $d_minor_category_id;
          }
        }

        $previous_year_product->field_data_source->value = $record[11];
        $previous_year_product->field_healthier_option->value = $record[12];
        $previous_year_product->field_serving_unit->value = $record[13];
        $previous_year_product->field_serving_size->value = $record[14];
        $previous_year_product->field_package_unit->value = $record[15];
        $previous_year_product->field_package_size->value = $record[16];
        $previous_year_product->field_energy_kj_100g->value = $record[17];
        $previous_year_product->field_protein_g_100g->value = $record[18];
        $previous_year_product->field_total_fat_g_100g->value = $record[19];
        $previous_year_product->field_saturated_fat_g_100g->value = $record[20];
        $previous_year_product->field_carbohydrate_g_100g->value = $record[21];
        $previous_year_product->field_sugar_g_100g->value = $record[22];
        $previous_year_product->field_sodium_mg_100g->value = $record[23];
        $previous_year_product->field_fibre_g_100g->value = $record[24];
        $previous_year_product->field_trans_fat_g_100g->value = $record[25];
        $previous_year_product->field_poly_fat_g_100g->value = $record[26];
        $previous_year_product->field_mono_fat_g_100g->value = $record[27];
        $previous_year_product->field_energy_kj_serve->value = $record[28];
        $previous_year_product->field_protein_g_serve->value = $record[29];
        $previous_year_product->field_total_fat_g_serve->value = $record[30];
        $previous_year_product->field_saturated_fat_g_serve->value = $record[31];
        $previous_year_product->field_carbohydrate_g_serve->value = $record[32];
        $previous_year_product->field_sugar_g_serve->value = $record[33];
        $previous_year_product->field_sodium_mg_serve->value = $record[34];
        $previous_year_product->field_fibre_g_serve->value = $record[35];
        $previous_year_product->field_trans_fat_g_serve->value = $record[36];
        $previous_year_product->field_poly_fat_g_serve->value = $record[37];
        $previous_year_product->field_mono_fat_g_serve->value = $record[38];
        $previous_year_product->field_notes_on_missing_data->value = $record[39];
        $previous_year_product->field_additional_notes->value = $record[40];
        $previous_year_product->field_all_information->value = TRUE;

        $previous_year_product->save();

        $new_nodes_created++;
      }
    }
  }

  ////////////***************************************************////////////

  /**
   * Use a copy of the Master Product content type to create a new Product content type for the current year.
   * 
   * @param $year
   *  Argument provided to the drush command
   * 
   * @command drush_test:copyMPcreatePROD
   * @aliases d-copyMPcreatePROD
   * @usage drush_test:copyMPcreatePROD 2022
   *    Creates a new 2022 Product content type from the Master Product template
   */
  function copyMPcreatePROD($year) {

    // Use an entityQuery to get all the content/node ids of master product content type 
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type','master_product');
    
    $nids = $query->execute();
    
    // Get current year for using in the loop below
    // $curr_year = date('Y');
    
    $i = 0; // Variable for counting items processed
    $copied_products_created = 0; // Counter variable for counting nodes created
    
    // Create node storage to be used in the loop below
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    
    // Use a loop to process each nid and create a product content type for the current year
    foreach ($nids as $nid) {
      
      // Load the master products one by one
      $master_product = $node_storage->load($nid);
      
      // Create the copy product and assign the values from the corresponding master product
      $copied_product = $node_storage
        ->create([
          'type' => 'product',
          'title' => $master_product->title->value, 
          'uid' => 1]);
      
      // Assign each value from master product fields to the copy product fields.
      $copied_product->field_product_id->value = $master_product->field_master_product_id->value;
      $copied_product->field_product_name->value = $master_product->field_master_product_name->value;
      $copied_product->field_year->value = $year;
      $copied_product->field_fast_food_chain->target_id = $master_product->field_master_fast_food_chain->target_id;
      $copied_product->field_food_group->target_id = $master_product->field_master_food_group->target_id;
      
      $copied_product->save();
      
      $i++; 
      $copied_products_created++;
    }

    $this->output()->writeln('Total items processed: ' . $i);
    $this->output()->writeln('Total new products created: ' . $copied_products_created);
    
  }

  ////////////***************************************************////////////

  /**
   * Function to get Drupal Fast-Food Chain Id from Database Fast-Food Chain ID
   * @param $fast_food_chain_id
   */
  function getDrupalFastFoodChainID($fast_food_chain_id) {
    $query = \Drupal::entityQuery('node')
     ->condition('type', 'fast_food_chain')
     ->condition('field_fast_food_chain_id', $fast_food_chain_id);

    $nids = $query->execute();
    
    foreach ($nids as $nid) {
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      $ffc_id = $node->id();
      return $ffc_id;
      break;
    }
    return FALSE;
  }

  /**
   * Function to get Drupal Food Group ID from Database Food Group ID
   * @param $food_group_id
   */
  function getDrupalFoodGroupID($food_group_id) {
    $query_tid = \Drupal::entityQuery('taxonomy_term')
     ->condition('vid', 'food_group')
     ->condition('field_food_group_id', $food_group_id);
    
    $tids = $query_tid->execute();
    
     foreach ($tids as $tid) {
      $term_id = $tid;
      break;
    }
    return $term_id;
  }

  /**
   * Function to get Drupal Major Category ID from Database Major Category ID
   * @param $major_category_id
   */
  function getDrupalMajorCategoryID($major_category_id) {
    $query_tid = \Drupal::entityQuery('taxonomy_term')
     ->condition('vid', 'food_group')
     ->condition('field_major_category_id', $major_category_id);
    
    $tids = $query_tid->execute();
    
    foreach ($tids as $tid) {
      $term_id = $tid;
      break;
    }
    return $term_id;
  }

  /**
   * Function to get Drupal Minor Category ID from Database Minor Category ID
   * @param $minor_category_id
   */
  function getDrupalMinorCategoryID($minor_category_id) {
    $query_tid = \Drupal::entityQuery('taxonomy_term')
     ->condition('vid', 'food_group')
     ->condition('field_minor_category_id', $minor_category_id);    
    
    $tids = $query_tid->execute();
    
    foreach ($tids as $tid) {
      $term_id = $tid;
      break;
    }
    return $term_id;
  }

}
